<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Support\Context\TenantContext;

class StaffController extends Controller
{
    private TenantContext $context;

    public function __construct(TenantContext $context)
    {
        $this->context = $context;
    }

    public function index()
    {
        $user = $this->context->getClinicOwner();
        $user->load('plan');

        $staff = User::where('doctor_id', $user->id) // owner id
            ->where('role', 'staff')
            ->get();
            
        // Strict: Use DB column
        $maxStaff = $user->plan->max_staff;
        
        return response()->json([
            'data' => $staff,
            'meta' => [
                'count'     => $staff->count(),
                'max_staff' => $maxStaff,
                'plan_name' => $user->plan->name ?? 'Free'
            ]
        ]);
    }

    /**
     * GET /staff/me
     * Returns the authenticated staff member's own profile and permissions.
     * Accessible by staff without requiring 'settings' permission.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'staff') {
            return response()->json(['message' => 'This endpoint is for staff members only'], 403);
        }

        return response()->json([
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'role'        => $user->role,
            'role_type'   => $user->role_type,
            'permissions' => $user->permissions ?? [],
            'doctor_id'   => $user->doctor_id,
            'created_at'  => $user->created_at,
        ]);
    }

    public function store(Request $request)
    {
        $user = $this->context->getClinicOwner();

        // 1. Validate Input (Crucial Security)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string',
            'role_type' => 'required|in:assistant,receptionist',
            'permissions' => 'sometimes|array',
        ]);

        return DB::transaction(function () use ($request, $user, $validated) {
            
            // 2. Lock Subscription Plan for Update (Prevents Limit Modification Race Condition)
            $plan = DB::table('subscription_plans')
                ->where('id', $user->plan_id)
                ->lockForUpdate()
                ->first();

            if (!$plan) {
                return response()->json(['message' => 'Subscription plan not found.'], 403);
            }

            // 3. Count Active Staff (Strict Read)
            // Note: In strict concurrency, we might want to lock user row or staff rows too, but strict plan lock is a good start.
            $currentStaffCount = DB::table('users')
                ->where('doctor_id', $user->id)
                ->where('role', 'staff')
                ->where('is_active', true)
                ->whereNull('deleted_at') // Exclude soft-deleted users
                ->count();

            // 4. Enforce Limit
            $limit = $plan->max_staff;
            if ($limit !== -1 && $limit !== null && $currentStaffCount >= $limit) {
                return response()->json(['message' => "Staff limit reached for your subscription plan ({$limit}). Please upgrade."], 422);
            }

            // 5. Create Staff Safely
            $staff = User::create([
                'doctor_id' => $user->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'staff',
                'role_type' => $validated['role_type'],
                'is_active' => true,
                'plan_id' => $user->plan_id,
                'permissions' => $validated['permissions'] ?? null,
            ]);

            // 6. Log Action (Moved inside transaction for consistency)
            \App\Models\AuditLog::log('staff_created', "Created staff member {$staff->name} as {$staff->role_type}", ['staff_id' => $staff->id]);

            return response()->json($staff, 201);
        });
    }

    public function show($id)
    {
        $user = User::where('doctor_id', $this->context->getClinicOwner()->id)->findOrFail($id);
        
        if ($user->doctor_id !== $this->context->getClinicOwner()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::where('doctor_id', $this->context->getClinicOwner()->id)->findOrFail($id);

        if ($user->doctor_id !== $this->context->getClinicOwner()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string',
            'role_type' => 'sometimes|in:assistant,receptionist',
            'password' => 'nullable|string|min:8',
            'is_active' => 'sometimes|boolean',
            'permissions' => 'sometimes|array'
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        \App\Models\AuditLog::log('staff_updated', "Updated staff member {$user->name}", ['staff_id' => $user->id, 'changes' => array_keys($validated)]);

        return response()->json($user);
    }

    public function destroy($id)
    {
        try {
            return response()->json(app(DeleteManager::class)->delete('staff', $id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Get activity logs for a specific staff member.
     */
    public function activity($id)
    {
        $user = User::where('doctor_id', $this->context->getClinicOwner()->id)->findOrFail($id);
        
        // Security: Doctor can only view their own staff's logs
        if ($user->doctor_id !== $this->context->getClinicOwner()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $logs = $user->auditLogs()
            ->latest()
            ->limit(50)
            ->get();

        return response()->json($logs);
    }
}

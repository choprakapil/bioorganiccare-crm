<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;

use App\Support\Context\TenantContext;

class SettingsController extends Controller
{
    protected TenantContext $context;

    public function __construct(TenantContext $context)
    {
        $this->context = $context;
    }

    /**
     * Get current branding and clinic settings
     */
    public function getSettings()
    {
        $user = $this->context->getClinicOwner();

        if (!$user) {
            return response()->json(['message' => 'Clinic context required.'], 422);
        }
        return response()->json([
            'clinic_name' => $user->clinic_name,
            'clinic_logo' => $user->clinic_logo,
            'brand_color' => $user->brand_color,
            'brand_secondary_color' => $user->brand_secondary_color,
            'phone' => $user->phone,
            'name' => $user->name,
            'email' => $user->email,
            'plan' => $user->plan ? [
                'name' => $user->plan->name,
                'max_patients' => $user->plan->max_patients,
                'max_appointments' => $user->plan->max_appointments_monthly
            ] : null
        ]);
    }

    /**
     * Update branding and white-label settings
     */
    public function updateBranding(Request $request)
    {
        $user = $this->context->getClinicOwner();

        if (!$user) {
             return response()->json(['message' => 'Clinic context required.'], 422);
        }

        $validated = $request->validate([
            'clinic_name' => 'sometimes|string|max:255',
            'brand_color' => 'sometimes|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'brand_secondary_color' => 'sometimes|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
        ]);

        $user->update($validated);

        AuditLog::log(
            'branding_updated',
            "Updated clinic branding: {$user->clinic_name}",
            ['colors' => [$user->brand_color, $user->brand_secondary_color]]
        );

        return response()->json([
            'message' => 'Branding updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Placeholder for logo upload
     */
    public function updateLogo(Request $request)
    {
        // For local dev, we could store base64 or move to S3/Public storage in production
        $request->validate(['logo' => 'required|string']); // Assuming base64 for now for simplicity in hardening v1
        
        $user = $this->context->getClinicOwner();

        if (!$user) {
             return response()->json(['message' => 'Clinic context required.'], 422);
        }
        $user->update(['clinic_logo' => $request->logo]);

        return response()->json(['message' => 'Logo updated successfully']);
    }

    /**
     * Update self profile (name, password)
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user(); // Self update
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:6',
        ]);

        $user->name = $validated['name'];
        
        if (!empty($validated['password'])) {
            $user->password = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
}

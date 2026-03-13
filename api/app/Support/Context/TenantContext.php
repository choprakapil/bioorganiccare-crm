<?php

namespace App\Support\Context;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Specialty;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class TenantContext
{
    private ?User $authUser = null;
    private ?User $clinicOwner = null;
    private ?Specialty $specialty = null;
    private ?SubscriptionPlan $plan = null;
    private ?array $enabledModules = null;

    public function resolve(Request $request): void
    {
        $this->authUser = Auth::user();
        if (!$this->authUser) {
            return;
        }

        // 1. Resolve Clinic Owner (Doctor identity)
        $this->clinicOwner = $this->authUser->role === 'staff'
            ? User::find($this->authUser->doctor_id)
            : $this->authUser;

        if (!$this->clinicOwner && $this->authUser->role !== 'super_admin') {
            throw new RuntimeException('Tenant Context Error: Unable to resolve Clinic Owner.');
        }

        // 2. Resolve Specialty ID (Strict algorithm)
        $this->resolveSpecialty($request);

        // 3. Resolve Subscription Plan
        if ($this->clinicOwner) {
            $this->plan = $this->clinicOwner->plan;
        }
    }

    private function resolveSpecialty(Request $request): void
    {
        // Rule 1: Super Admin explicitly requesting a specialty
        if ($this->authUser->role === 'super_admin') {
            $routeSpecialty = $request->route('specialty');
            if ($routeSpecialty instanceof Specialty) {
                $this->specialty = $routeSpecialty;
                return;
            }

            $payloadId = $request->input('specialty_id') ?? $request->query('specialty_id');
            if ($payloadId) {
                // withTrashed() allows super admin to inspect archived specialties
                $this->specialty = Specialty::withTrashed()->find($payloadId);
                return;
            }

            // DO NOT allow Admin null specialty silently without warning (Phase 1 constraint)
            Log::warning('Admin API accessed without specialty_id context in Route or Payload.');
            $this->specialty = null;
            return;
        }

        // Rule 2/3: Doctor/Staff MUST have native context
        if (!$this->clinicOwner || !$this->clinicOwner->specialty_id) {
            throw new RuntimeException('Tenant Context Error: Clinic Owner lacks a Specialty assignment.');
        }

        // Staff Integrity Check: Ensure staff cannot drift from parent doctor specialty
        if ($this->authUser->role === 'staff' && $this->authUser->specialty_id) {
            if ($this->authUser->specialty_id !== $this->clinicOwner->specialty_id) {
                throw new RuntimeException("Tenant Context Error: Security violation. Staff specialty ({$this->authUser->specialty_id}) mismatches Doctor specialty ({$this->clinicOwner->specialty_id}).");
            }
        }

        $this->specialty = $this->clinicOwner->specialty;

        // Guard: Specialty exists in DB but has been soft-deleted
        if ($this->specialty && $this->specialty->trashed()) {
            throw new RuntimeException(
                'Specialty has been deactivated. Please contact administrator.'
            );
        }

        // Guard: specialty_id set on owner but relation resolves to null (orphaned FK)
        if (!$this->specialty && $this->clinicOwner->specialty_id) {
            throw new RuntimeException(
                'Tenant Context Error: Specialty could not be resolved for Clinic Owner.'
            );
        }
    }

    public function getAuthUser(): ?User { return $this->authUser; }
    public function getClinicOwner(): ?User { return $this->clinicOwner; }
    public function getSpecialtyId(): ?int { return $this->specialty?->id; }
    public function getSpecialty(): ?Specialty { return $this->specialty; }
    public function getPlan(): ?SubscriptionPlan { return $this->plan; }

    public function getEnabledModules(): array
    {
        if ($this->enabledModules !== null) {
            return $this->enabledModules;
        }

        if (!$this->authUser) {
            return [];
        }

        if ($this->authUser->role === 'super_admin') {
            return $this->enabledModules = Cache::remember('super_admin_modules', now()->addMinutes(5), function () {
                return \App\Models\Module::pluck('key')->toArray();
            });
        }

        if (!$this->plan || !$this->specialty) {
            return $this->enabledModules = [];
        }

        // Load modules if not eagerly loaded
        if (!$this->plan->relationLoaded('modules')) {
            $this->plan->load('modules');
        }
        if (!$this->specialty->relationLoaded('modules')) {
            $this->specialty->load('modules');
        }

        // Improved cache key strategy fixing the propagation delay
        $specialtyUpdatedAt = $this->specialty->updated_at?->timestamp ?? 0;
        $planUpdatedAt = $this->plan->updated_at?->timestamp ?? 0;
        $globalMap = Cache::get('saas_module_schema_version', 0);

        $cacheKey = "doctor_modules_{$this->clinicOwner->id}_{$this->specialty->id}_{$specialtyUpdatedAt}_{$planUpdatedAt}_{$globalMap}";

        return $this->enabledModules = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            Log::info("COMPUTING MODULES for doctor {$this->clinicOwner->id}");
            
            $planModules = $this->plan->modules
                ->where('pivot.enabled', true)
                ->pluck('key')
                ->toArray();

            $specialtyModules = $this->specialty->modules
                ->where('pivot.enabled', true)
                ->pluck('key')
                ->toArray();

            // Intersect ensures they only get it if both the specialty supports it and the plan includes it
            return array_values(array_intersect($planModules, $specialtyModules));
        });
    }
}

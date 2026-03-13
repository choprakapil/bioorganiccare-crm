<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\ModuleCacheService;

use App\Models\Traits\ProtectedDeletion;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, ProtectedDeletion;

    protected static function booted()
    {
        static::updated(function ($user) {
            // Evict module cache when doctor's plan or specialty changes.
            // Uses schema version bump — guaranteed to bust the 5-part TenantContext key
            // regardless of the specialty/plan timestamps embedded in the key suffix.
            if ($user->wasChanged(['plan_id', 'specialty_id'])) {
                \Illuminate\Support\Facades\Log::info("[UserModel] plan_id/specialty_id changed for user #{$user->id} — busting module cache.");
                ModuleCacheService::invalidateByDoctor($user->id);
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'doctor_id',
        'role',
        'is_active',
        'specialty_id',
        'plan_id',
        'phone',
        'clinic_name',
        'clinic_logo',
        'brand_color',
        'brand_secondary_color',
        'role_type',
        'permissions',
        'subscription_started_at',
        'subscription_renews_at',
        'subscription_grace_ends_at',
        'subscription_status',
        'billing_interval',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'subscription_started_at' => 'datetime',
            'subscription_renews_at' => 'datetime',
            'subscription_grace_ends_at' => 'datetime',
        ];
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function patients()
    {
        return $this->hasMany(Patient::class, 'doctor_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    public function serviceSettings()
    {
        return $this->hasMany(DoctorServiceSetting::class, 'user_id');
    }

    public function staff()
    {
        return $this->hasMany(User::class, 'doctor_id')->where('role', 'staff');
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Compute the intersection of available modules between the Doctor's Plan and Specialty.
     * Returns an array of enabled module keys.
     */
    /**
     * Cache computed modules per request to avoid redundant processing.
     * Note: This inline caching is now deprecated in favor of TenantContext.
     */
    protected array|null $computedModules = null;

    public function getEnabledModulesAttribute(): array
    {
        // Safe delegation to the centralized TenantContext service
        if (app()->bound(\App\Support\Context\TenantContext::class)) {
            $context = app(\App\Support\Context\TenantContext::class);
            // If the context matches the current user, or clinic owner is this user
            if (($context->getAuthUser() && $context->getAuthUser()->id === $this->id) ||
                ($context->getClinicOwner() && $context->getClinicOwner()->id === $this->id)) {
                return $context->getEnabledModules();
            }
        }

        // Ephemeral resolution for commands/jobs where HTTP context isn't available
        $ephemeralContext = new \App\Support\Context\TenantContext();
        // Since we cannot run standard Request resolution, we must mimic auth
        // In a real refactor, TenantContext could be broken down further, 
        // but for Phase 1 backwards compatibility, we replicate the exact old logic:
        if ($this->role === 'super_admin') {
            return \Illuminate\Support\Facades\Cache::remember('super_admin_modules', now()->addMinutes(10), function () {
                return \App\Models\Module::pluck('key')->toArray();
            });
        }

        if ($this->role === 'staff' && $this->doctor_id) {
            $doctor = self::with(['plan.modules', 'specialty.modules'])->find($this->doctor_id);
            return $doctor ? $doctor->enabled_modules : [];
        }

        if (!$this->plan || !$this->specialty) {
            return [];
        }

        // Apply fixing cache key logic to the inline fallback as well
        $specialtyUpdatedAt = $this->specialty->updated_at?->timestamp ?? 0;
        $planUpdatedAt = $this->plan->updated_at?->timestamp ?? 0;
        $cacheKey = "doctor_modules_{$this->id}_{$this->specialty->id}_{$specialtyUpdatedAt}_{$planUpdatedAt}";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(10), function () {
            \Illuminate\Support\Facades\Log::info("COMPUTING MODULES for doctor {$this->id} (Fallback)");
            $planModules = $this->plan->modules
                ->where('pivot.enabled', true)
                ->pluck('key')
                ->toArray();

            $specialtyModules = $this->specialty->modules
                ->where('pivot.enabled', true)
                ->pluck('key')
                ->toArray();

            return array_values(array_intersect($planModules, $specialtyModules));
        });
    }
}

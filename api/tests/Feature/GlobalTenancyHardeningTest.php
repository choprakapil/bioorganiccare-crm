<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Expense;
use App\Models\Specialty;
use App\Models\Module;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalTenancyHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Cache::flush();
        
        $this->specialty = Specialty::create(['name' => 'General', 'slug' => 'gen', 'is_active' => true]);
        $this->module = Module::create(['name' => 'Patients', 'key' => 'patient_registry', 'is_active' => true]);
        $this->specialty->modules()->attach($this->module->id, ['enabled' => true]);
        
        $this->plan = SubscriptionPlan::create([
            'name' => 'Pro', 'specialty_id' => $this->specialty->id, 'tier' => 'pro', 'is_active' => true, 'max_patients' => -1
        ]);

        $this->doctor = User::create([
            'name' => 'Dr. Owner', 'email' => 'owner@test.com', 'password' => bcrypt('password'),
            'role' => 'doctor', 'specialty_id' => $this->specialty->id, 'plan_id' => $this->plan->id,
            'subscription_status' => 'active', 'subscription_started_at' => now(), 'subscription_renews_at' => now()->addYear()
        ]);

        $this->otherDoctor = User::create([
            'name' => 'Dr. Other', 'email' => 'other@test.com', 'password' => bcrypt('password'),
            'role' => 'doctor', 'specialty_id' => $this->specialty->id, 'plan_id' => $this->plan->id,
            'subscription_status' => 'active', 'subscription_started_at' => now(), 'subscription_renews_at' => now()->addYear()
        ]);
    }

    /** @test */
    public function cannot_inject_different_doctor_id_via_patient_creation()
    {
        $this->actingAs($this->doctor);

        // Attempting to create a patient for $otherDoctor
        $response = $this->withoutMiddleware()->postJson('/api/patients', [
            'name' => 'Should Be Mine',
            'phone' => '1234567890',
            'doctor_id' => $this->otherDoctor->id // INJECTION ATTEMPT
        ]);

        $response->assertStatus(201);
        
        $patient = Patient::where('name', 'Should Be Mine')->first();
        
        // Assert it was assigned to the AUTHENTICATED doctor, not the INJECTED one
        $this->assertEquals($this->doctor->id, $patient->doctor_id);
    }

    /** @test */
    public function staff_creation_auto_assigns_correct_doctor_id()
    {
        $staff = User::create([
            'name' => 'Staff Nurse', 'email' => 'staff@test.com', 'password' => bcrypt('password'),
            'role' => 'staff', 'doctor_id' => $this->doctor->id, 'specialty_id' => $this->specialty->id,
            'permissions' => ['patients' => true]
        ]);

        $this->actingAs($staff);

        $response = $this->withoutMiddleware()->postJson('/api/patients', [
            'name' => 'Staff Registered',
            'phone' => '0987654321'
        ]);

        $response->assertStatus(201);

        $patient = Patient::where('name', 'Staff Registered')->first();
        
        // Assert it was assigned to the staff's doctor
        $this->assertEquals($this->doctor->id, $patient->doctor_id);
    }

    /** @test */
    public function mass_assignment_is_blocked_on_all_critical_models()
    {
        $this->actingAs($this->doctor);

        // Test Expense model
        $expense = Expense::create([
            'category' => 'Supplies',
            'amount' => 100,
            'expense_date' => now(),
            'doctor_id' => $this->otherDoctor->id // Attempted injection
        ]);

        $this->assertEquals($this->doctor->id, $expense->doctor_id);
    }
}

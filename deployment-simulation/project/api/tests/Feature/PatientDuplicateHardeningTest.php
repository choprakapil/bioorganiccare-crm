<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Specialty;
use App\Models\Module;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientDuplicateHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup for plan limits if needed
        $this->specialty = Specialty::create(['name' => 'General', 'slug' => 'gen', 'is_active' => true]);
        $this->module = Module::create(['name' => 'Patients', 'key' => 'patient_registry', 'is_active' => true]);
        $this->specialty->modules()->attach($this->module->id, ['enabled' => true]);
        $this->plan = SubscriptionPlan::create([
            'name' => 'Unlimited', 
            'specialty_id' => $this->specialty->id, 
            'tier' => 'pro', 
            'is_active' => true,
            'max_patients' => -1 // Unlimited
        ]);
        $this->plan->modules()->attach($this->module->id, ['enabled' => true]);

        $this->doctor1 = User::create([
            'name' => 'Dr1', 'email' => 'dr1@test.com', 'password' => bcrypt('password'),
            'role' => 'doctor', 'specialty_id' => $this->specialty->id, 'plan_id' => $this->plan->id
        ]);

        $this->doctor2 = User::create([
            'name' => 'Dr2', 'email' => 'dr2@test.com', 'password' => bcrypt('password'),
            'role' => 'doctor', 'specialty_id' => $this->specialty->id, 'plan_id' => $this->plan->id
        ]);
    }

    /** @test */
    public function cannot_create_duplicate_patient_for_same_doctor()
    {
        $this->actingAs($this->doctor1);

        // First patient
        $this->postJson('/api/patients', [
            'name' => 'John Doe',
            'phone' => '1234567890'
        ])->assertStatus(201);

        // Second patient with SAME phone
        $response = $this->postJson('/api/patients', [
            'name' => 'Another John',
            'phone' => '1234567890'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
    }

    /** @test */
    public function can_create_same_phone_patient_for_different_doctors()
    {
        // Doctor 1 creates patient
        $this->actingAs($this->doctor1);
        $this->postJson('/api/patients', [
            'name' => 'John Doe',
            'phone' => '1234567890'
        ])->assertStatus(201);

        // Doctor 2 creates patient with SAME phone
        $this->actingAs($this->doctor2);
        $this->postJson('/api/patients', [
            'name' => 'John Doe',
            'phone' => '1234567890'
        ])->assertStatus(201);

        $this->assertEquals(2, Patient::where('phone', '1234567890')->count());
    }

    /** @test */
    public function update_ignores_own_phone_but_blocks_other_duplicates()
    {
        $this->actingAs($this->doctor1);

        $p1 = Patient::create(['doctor_id' => $this->doctor1->id, 'name' => 'P1', 'phone' => '111']);
        $p2 = Patient::create(['doctor_id' => $this->doctor1->id, 'name' => 'P2', 'phone' => '222']);

        // Update P1 to own phone (should pass)
        $this->putJson("/api/patients/{$p1->id}", [
            'name' => 'P1 Updated',
            'phone' => '111'
        ])->assertStatus(200);

        // Update P1 to P2's phone (should fail)
        $this->putJson("/api/patients/{$p1->id}", [
            'name' => 'P1 Updated',
            'phone' => '222'
        ])->assertStatus(422)->assertJsonValidationErrors(['phone']);
    }
}

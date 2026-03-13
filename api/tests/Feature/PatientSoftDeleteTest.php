<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\Specialty;
use App\Models\Module;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->specialty = Specialty::create(['name' => 'General', 'slug' => 'gen', 'is_active' => true]);
        $this->module = Module::create(['name' => 'Patients', 'key' => 'patient_registry', 'is_active' => true]);
        $this->specialty->modules()->attach($this->module->id, ['enabled' => true]);
        
        $this->plan = SubscriptionPlan::create([
            'name' => 'Pro', 'specialty_id' => $this->specialty->id, 'tier' => 'pro', 'is_active' => true, 'max_patients' => -1
        ]);
        $this->plan->modules()->attach($this->module->id, ['enabled' => true]);

        $this->doctor = User::create([
            'name' => 'Dr. Soft', 'email' => 'soft@test.com', 'password' => bcrypt('password'),
            'role' => 'doctor', 'specialty_id' => $this->specialty->id, 'plan_id' => $this->plan->id
        ]);

        $this->actingAs($this->doctor);
    }

    /** @test */
    public function deleting_patient_retains_invoices_due_to_soft_delete()
    {
        $patient = Patient::create(['doctor_id' => $this->doctor->id, 'name' => 'John Doe']);
        $invoice = Invoice::create([
            'doctor_id' => $this->doctor->id, 
            'patient_id' => $patient->id,
            'total_amount' => 100,
            'balance_due' => 100
        ]);

        // Soft delete patient
        $response = $this->deleteJson("/api/patients/{$patient->id}");
        $response->assertStatus(200);

        // Verify patient is soft deleted but invoice remains
        $this->assertSoftDeleted('patients', ['id' => $patient->id]);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    /** @test */
    public function soft_deleted_patient_can_be_restored()
    {
        $patient = Patient::create(['doctor_id' => $this->doctor->id, 'name' => 'John Doe']);
        $patient->delete();
        
        $this->assertSoftDeleted('patients', ['id' => $patient->id]);

        // Restore
        $response = $this->postJson("/api/patients/{$patient->id}/restore");
        $response->assertStatus(200);

        $this->assertNotSoftDeleted('patients', ['id' => $patient->id]);
        $this->assertEquals('John Doe', $patient->fresh()->name);
    }

    /** @test */
    public function soft_deleted_patients_are_excluded_from_index()
    {
        Patient::create(['name' => 'Active']);
        $deleted = Patient::create(['name' => 'Deleted']);
        $deleted->delete();

        $response = $this->getJson('/api/patients');
        $response->assertStatus(200);
        
        // Index is now paginated
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Active', $data[0]['name']);
    }

    /** @test */
    public function force_deleting_patient_with_invoices_is_blocked_by_db_constraint()
    {
        $patient = Patient::create(['doctor_id' => $this->doctor->id, 'name' => 'Protected']);
        Invoice::create([
            'doctor_id' => $this->doctor->id, 
            'patient_id' => $patient->id,
            'total_amount' => 100
        ]);

        // DB level check: try to hard delete the patient record while invoice exists
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // Manual DB delete to bypass Eloquent's soft delete logic for testing the constraint
        \DB::table('patients')->where('id', $patient->id)->delete();
    }
}

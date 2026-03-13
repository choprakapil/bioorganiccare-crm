<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Specialty;
use App\Models\Module;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientPerformanceTest extends TestCase
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
            'name' => 'Dr. Speed', 'email' => 'speed@test.com', 'password' => bcrypt('password'),
            'role' => 'doctor', 'specialty_id' => $this->specialty->id, 'plan_id' => $this->plan->id
        ]);

        $this->actingAs($this->doctor);
    }

    /** @test */
    public function patient_index_is_paginated_and_lean()
    {
        // Create 25 patients manually since factory doesn't exist
        for ($i = 1; $i <= 25; $i++) {
            Patient::create([
                'doctor_id' => $this->doctor->id,
                'name' => "Patient $i",
                'phone' => "123456$i"
            ]);
        }

        $response = $this->getJson('/api/patients');
        $response->assertStatus(200);
        
        // Assert pagination structure
        $response->assertJsonStructure([
            'data', 'current_page', 'last_page', 'total'
        ]);
        
        $data = $response->json();
        $this->assertCount(20, $data['data']); // Default pagination 20
        $this->assertEquals(25, $data['total']);

        // Assert invoices are NOT eager loaded by default
        $this->assertArrayNotHasKey('invoices', $data['data'][0]);
    }

    /** @test */
    public function full_endpoint_loads_all_relations()
    {
        $patient = Patient::create(['doctor_id' => $this->doctor->id, 'name' => 'Big Payload']);
        $invoice = Invoice::create(['doctor_id' => $this->doctor->id, 'patient_id' => $patient->id, 'total_amount' => 100]);
        InvoiceItem::create([
            'invoice_id' => $invoice->id, 
            'name' => 'Service X', 
            'type' => 'Procedure',
            'quantity' => 1, 
            'unit_price' => 100, 
            'fee' => 100
        ]);

        $response = $this->getJson("/api/patients/{$patient->id}/full");
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals('Big Payload', $data['name']);
        
        // Assert relations are present
        $this->assertArrayHasKey('invoices', $data);
        $this->assertArrayHasKey('items', $data['invoices'][0]);
        $this->assertArrayHasKey('appointments', $data);
        $this->assertArrayHasKey('treatments', $data);
    }
}

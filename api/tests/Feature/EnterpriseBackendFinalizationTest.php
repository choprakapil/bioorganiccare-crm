<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Patient;
use App\Enums\InvoiceStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EnterpriseBackendFinalizationTest extends TestCase
{
    use RefreshDatabase;

    protected $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->doctor = User::factory()->create(['role' => 'doctor']);
    }

    /** @test */
    public function invoice_mutations_invalidate_relevant_caches()
    {
        $this->withoutMiddleware([\App\Http\Middleware\EnforcePlanLimits::class]);
        
        $patient = Patient::factory()->create(['doctor_id' => $this->doctor->id]);
        $invoice = Invoice::factory()->create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $patient->id,
            'status' => InvoiceStatus::UNPAID,
            'total_amount' => 100,
            'balance_due' => 100
        ]);

        $cacheKey = "finance_summary_{$this->doctor->id}";
        Cache::put($cacheKey, ['data' => 'stale'], 3600);

        $this->actingAs($this->doctor)
            ->postJson("/api/invoices/{$invoice->id}/finalize")
            ->assertStatus(200);

        $this->assertNull(Cache::get($cacheKey), "Cache should be invalidated after finalization");
    }

    /** @test */
    public function audit_logs_use_cursor_pagination()
    {
        $this->withoutMiddleware([\App\Http\Middleware\EnforcePlanLimits::class]);
        
        User::factory()->count(10)->create(['doctor_id' => $this->doctor->id]);
        
        // Creating some logs
        \App\Models\AuditLog::log('test_action', 'desc', [], $this->doctor->id);
        \App\Models\AuditLog::log('test_action2', 'desc', [], $this->doctor->id);

        $response = $this->actingAs($this->doctor)
            ->getJson("/api/audit-logs?per_page=1")
            ->assertStatus(200);

        $response->assertJsonStructure([
            'data',
            'next_cursor',
            'prev_cursor'
        ]);

        // Verify it's not traditional pagination
        $response->assertJsonMissing(['total', 'last_page']);
    }

    /** @test */
    public function health_endpoint_reports_queue_status()
    {
        $response = $this->getJson("/api/health")
            ->assertStatus(200);

        $response->assertJsonStructure([
            'database',
            'redis',
            'queue_status',
            'queue_failed_count'
        ]);
    }

    /** @test */
    public function analytics_service_handles_db_failure_via_circuit_breaker()
    {
        $service = new \App\Services\AnalysisService();
        $cacheKey = "finance_summary_v2_{$this->doctor->id}";
        Cache::put($cacheKey, ['metrics' => ['revenue' => ['current' => 999]], 'is_fallback' => true], 3600);

        // Force a DB error during the next query
        DB::shouldReceive('table')->andThrow(new \Exception("DB Overload"));

        $result = $service->calculateFinancialSummary($this->doctor);

        $this->assertTrue($result['is_fallback'], "Circuit breaker should provide fallback");
        $this->assertEquals(999, $result['metrics']['revenue']['current'], "Should return data from v2 cache");
    }

    /** @test */
    public function finance_summary_calculates_accurate_change_percent()
    {
        $this->withoutMiddleware([\App\Http\Middleware\EnforcePlanLimits::class]);

        // Current period (today)
        Invoice::factory()->create([
            'doctor_id' => $this->doctor->id,
            'total_amount' => 150,
            'paid_amount' => 150,
            'created_at' => now()
        ]);

        // Previous period (95 days ago)
        Invoice::factory()->create([
            'doctor_id' => $this->doctor->id,
            'total_amount' => 100,
            'paid_amount' => 100,
            'created_at' => now()->subDays(95)
        ]);

        $response = $this->actingAs($this->doctor)
            ->getJson("/api/finance/summary")
            ->assertStatus(200);

        $response->assertJsonPath('metrics.revenue.current', 150);
        $response->assertJsonPath('metrics.revenue.previous', 100);
        $response->assertJsonPath('metrics.revenue.change_percent', 50);
    }

    /** @test */
    public function invoices_always_contain_persistent_uuid()
    {
        $this->withoutMiddleware([\App\Http\Middleware\EnforcePlanLimits::class]);
        
        $invoice = Invoice::factory()->create(['doctor_id' => $this->doctor->id]);
        
        $this->assertNotNull($invoice->uuid);
        $this->assertEquals(36, strlen($invoice->uuid));

        $this->actingAs($this->doctor)
            ->getJson("/api/invoices")
            ->assertStatus(200)
            ->assertJsonPath('data.0.uuid', $invoice->uuid);
    }
}

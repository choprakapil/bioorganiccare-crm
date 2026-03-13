<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class RevenueTrendTest extends TestCase
{
    use RefreshDatabase;

    protected $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->doctor = User::factory()->create(['role' => 'doctor']);
        Cache::clear();
        $this->withoutMiddleware([
            \App\Http\Middleware\EnforcePlanLimits::class,
            \App\Http\Middleware\CheckModuleAccess::class,
            'subscription',
            'module:auto'
        ]);
    }

    /** @test */
    public function it_returns_30_points_for_30_day_request()
    {
        $response = $this->actingAs($this->doctor)
            ->getJson('/api/finance/revenue-trend?days=30');

        $response->assertStatus(200);
        $response->assertJsonCount(30, 'points');
        $this->assertEquals(30, $response->json('window_days'));
    }

    /** @test */
    public function it_returns_90_points_for_default_request()
    {
        $response = $this->actingAs($this->doctor)
            ->getJson('/api/finance/revenue-trend');

        $response->assertStatus(200);
        $response->assertJsonCount(90, 'points');
        $this->assertEquals(90, $response->json('window_days'));
    }

    /** @test */
    public function it_correctly_aggregates_revenue_and_fills_zeros()
    {
        // One invoice today
        Invoice::factory()->create([
            'doctor_id' => $this->doctor->id,
            'total_amount' => 500,
            'created_at' => now(),
            'status' => 'Paid'
        ]);

        // One invoice yesterday
        Invoice::factory()->create([
            'doctor_id' => $this->doctor->id,
            'total_amount' => 300,
            'created_at' => now()->subDay(),
            'status' => 'Paid'
        ]);

        $response = $this->actingAs($this->doctor)
            ->getJson('/api/finance/revenue-trend?days=30');

        $response->assertStatus(200);
        
        $points = $response->json('points');
        
        // Last point (today)
        $this->assertEquals(500, $points[29]['revenue']);
        $this->assertEquals(now()->toDateString(), $points[29]['date']);

        // Second to last point (yesterday)
        $this->assertEquals(300, $points[28]['revenue']);
        $this->assertEquals(now()->subDay()->toDateString(), $points[28]['date']);

        // Third to last point should be zero
        $this->assertEquals(0, $points[27]['revenue']);
    }

    /** @test */
    public function it_enforces_doctor_isolation()
    {
        $otherDoctor = User::factory()->create(['role' => 'doctor']);
        
        Invoice::factory()->create([
            'doctor_id' => $otherDoctor->id,
            'total_amount' => 1000,
            'created_at' => now()
        ]);

        $response = $this->actingAs($this->doctor)
            ->getJson('/api/finance/revenue-trend?days=30');

        $response->assertStatus(200);
        $points = $response->json('points');
        
        // Current doctor should see 0 despite other doctor having revenue
        $this->assertEquals(0, $points[29]['revenue']);
    }

    /** @test */
    public function it_ignores_cancelled_invoices()
    {
        Invoice::factory()->create([
            'doctor_id' => $this->doctor->id,
            'total_amount' => 500,
            'created_at' => now(),
            'status' => 'cancelled'
        ]);

        $response = $this->actingAs($this->doctor)
            ->getJson('/api/finance/revenue-trend?days=30');

        $response->assertStatus(200);
        $points = $response->json('points');
        $this->assertEquals(0, $points[29]['revenue']);
    }
}

<?php

namespace App\Jobs;

use App\Models\User;
use App\Http\Controllers\GrowthInsightController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ComputeGrowthInsights implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $doctorId;

    public function __construct(int $doctorId)
    {
        $this->doctorId = $doctorId;
    }

    public function handle()
    {
        $doctor = User::find($this->doctorId);
        if (!$doctor) return;

        // Simulate heavy work by calling the logic or a service
        // For now, we'll just pre-calculate and cache it
        // We'll use the controller logic as a reference or move it to a Service
        
        $insights = (new \App\Services\AnalysisService())->calculateGrowthInsights($doctor);
        
        Cache::put("growth_insights_{$this->doctorId}", $insights, now()->addHours(1));
    }
}

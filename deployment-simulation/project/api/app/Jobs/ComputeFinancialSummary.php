<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\AnalysisService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ComputeFinancialSummary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $doctorId;

    public function __construct(int $doctorId)
    {
        $this->doctorId = $doctorId;
    }

    public function handle(AnalysisService $service)
    {
        $doctor = User::find($this->doctorId);
        if (!$doctor) return;

        $summary = $service->calculateFinancialSummary($doctor);
        
        Cache::put("finance_summary_v2_{$this->doctorId}", $summary, now()->addHours(6));
    }
}

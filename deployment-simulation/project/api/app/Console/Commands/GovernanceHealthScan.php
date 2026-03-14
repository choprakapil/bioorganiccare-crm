<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Admin\GovernanceController;
use Illuminate\Support\Facades\Log;

class GovernanceHealthScan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'governance:scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform system health checks and log issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting System Health Scan...');
        
        $controller = new GovernanceController();
        $response = $controller->health();
        $data = json_decode($response->getContent(), true);

        $status = $data['status'] ?? 'unknown';
        $issuesFound = $data['issues_found'] ?? 0;

        if ($status === 'healthy') {
            $this->info("System is healthy. Issues found: 0");
        } else {
            $message = "System health check returned [{$status}]. Issues found: {$issuesFound}";
            
            if ($status === 'critical') {
                $this->error($message);
                Log::critical($message, ['checks' => $data['checks']]);
            } else {
                $this->warn($message);
                Log::warning($message, ['checks' => $data['checks']]);
            }
        }

        $this->info('Scan complete.');
    }
}

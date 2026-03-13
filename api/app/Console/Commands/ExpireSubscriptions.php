<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ExpireSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update subscription statuses based on renewable dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Subscription Expiry Check...');
        
        // 1. Transition ACTIVE -> PAST_DUE
        // Find doctors who are active but past their renewal date
        $expiredActive = User::where('role', 'doctor')
            ->where('subscription_status', 'active')
            ->where('subscription_renews_at', '<', now())
            ->get();

        foreach ($expiredActive as $doctor) {
            $doctor->update([
                'subscription_status' => 'past_due',
                // consistent with middleware: 3 day grace period from detection (or from renews_at?)
                // Middleware uses `now()->addDays(3)`. To be consistent, we use now().
                'subscription_grace_ends_at' => now()->addDays(3) 
            ]);
            
            Log::info("Subscription marked PAST_DUE for Doctor ID {$doctor->id}. Grace period ends " . $doctor->subscription_grace_ends_at);
            $this->info("Doctor {$doctor->id} -> PAST_DUE");
        }

        // 2. Transition PAST_DUE -> EXPIRED
        // Find doctors who are past due and grace period has ended
        $expiredGrace = User::where('role', 'doctor')
            ->where('subscription_status', 'past_due')
            ->whereNotNull('subscription_grace_ends_at')
            ->where('subscription_grace_ends_at', '<', now())
            ->get();

        foreach ($expiredGrace as $doctor) {
            $doctor->update([
                'subscription_status' => 'expired'
            ]);
            
            Log::info("Subscription marked EXPIRED for Doctor ID {$doctor->id}. Grace period ended.");
            $this->info("Doctor {$doctor->id} -> EXPIRED");
        }

        $this->info('Subscription check complete.');
    }
}

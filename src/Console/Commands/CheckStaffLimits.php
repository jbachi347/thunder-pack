<?php

namespace ThunderPack\Console\Commands;

use ThunderPack\Services\LimitNotificationService;
use Illuminate\Console\Command;

class CheckStaffLimits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'limits:check-staff
                            {--threshold=90 : Minimum percentage to trigger notification}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all tenants for staff limit thresholds and send notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking staff limits for all tenants...');

        $notifiedCount = LimitNotificationService::checkAllTenants();

        $this->info("✓ Check completed. {$notifiedCount} tenants notified.");

        return Command::SUCCESS;
    }
}

<?php

namespace ThunderPack\Database\Seeders;

use ThunderPack\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'code' => 'solo',
                'name' => 'Solo',
                'staff_limit' => 1,
                'storage_quota_bytes' => 2 * 1024 * 1024 * 1024, // 2GB
                'monthly_price_cents' => 2900,
                'currency' => 'USD',
                'features' => [
                    // Legacy compatibility (also in columns)
                    'max_staff' => 1,
                    'max_storage_bytes' => 2 * 1024 * 1024 * 1024,
                    
                    // New dynamic limits
                    'max_clients' => 50,
                    'max_projects' => 10,
                    'max_whatsapp_phones' => 1,
                    'api_calls_per_month' => 5000,
                    'max_team_invitations' => 3,
                    
                    // Feature flags
                    'modules' => ['basic_reports'],
                    'custom_branding' => false,
                    'priority_support' => false,
                    'api_access' => false,
                    'bulk_import' => false,
                ],
            ],
            [
                'code' => 'team',
                'name' => 'Team',
                'staff_limit' => 5,
                'storage_quota_bytes' => 10 * 1024 * 1024 * 1024, // 10GB
                'monthly_price_cents' => 9900,
                'currency' => 'USD',
                'features' => [
                    // Legacy compatibility
                    'max_staff' => 5,
                    'max_storage_bytes' => 10 * 1024 * 1024 * 1024,
                    
                    // New dynamic limits
                    'max_clients' => 250,
                    'max_projects' => 50,
                    'max_whatsapp_phones' => 3,
                    'api_calls_per_month' => 25000,
                    'max_team_invitations' => 10,
                    
                    // Feature flags
                    'modules' => ['basic_reports', 'whatsapp', 'api'],
                    'custom_branding' => true,
                    'priority_support' => false,
                    'api_access' => true,
                    'bulk_import' => true,
                ],
            ],
            [
                'code' => 'agency',
                'name' => 'Agency',
                'staff_limit' => 15,
                'storage_quota_bytes' => 50 * 1024 * 1024 * 1024, // 50GB
                'monthly_price_cents' => 19900,
                'currency' => 'USD',
                'features' => [
                    // Legacy compatibility
                    'max_staff' => 15,
                    'max_storage_bytes' => 50 * 1024 * 1024 * 1024,
                    
                    // New dynamic limits (higher tiers)
                    'max_clients' => 1000,
                    'max_projects' => 200,
                    'max_whatsapp_phones' => 10,
                    'api_calls_per_month' => 100000,
                    'max_team_invitations' => 50,
                    
                    // Feature flags (all features)
                    'modules' => ['basic_reports', 'advanced_reports', 'whatsapp', 'api', 'analytics'],
                    'custom_branding' => true,
                    'priority_support' => true,
                    'api_access' => true,
                    'bulk_import' => true,
                    'white_label' => true,
                ],
            ],
        ];

        foreach ($plans as $p) {
            Plan::updateOrCreate(
                ['code' => $p['code']],
                $p
            );
        }
    }
}

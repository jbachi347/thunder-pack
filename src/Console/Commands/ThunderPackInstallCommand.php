<?php

namespace ThunderPack\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ThunderPackInstallCommand extends Command
{
    protected $signature = 'thunder-pack:install 
                            {--force : Overwrite existing files}
                            {--skip-migrations : Skip running migrations}
                            {--skip-seed : Skip seeding plans}';

    protected $description = 'Install Thunder Pack - Multi-tenant SaaS package';

    public function handle(): int
    {
        $this->displayBanner();

        $this->info('🚀 Starting Thunder Pack installation...');
        $this->newLine();

        // Step 1: Publish migrations
        if ($this->confirm('Publish database migrations?', true)) {
            $this->publishMigrations();
        }

        // Step 2: Publish config
        if ($this->confirm('Publish configuration file?', true)) {
            $this->publishConfig();
        }

        // Step 3: Publish views (optional)
        if ($this->confirm('Publish views? (optional - only if you want to customize)', false)) {
            $this->publishViews();
        }

        // Step 4: Run migrations
        if (!$this->option('skip-migrations')) {
            if ($this->confirm('Run migrations now?', true)) {
                $this->runMigrations();
            }
        }

        // Step 5: Seed plans
        if (!$this->option('skip-seed')) {
            if ($this->confirm('Seed default subscription plans?', true)) {
                $this->seedPlans();
            }
        }

        // Step 6: Create super admin (optional)
        if ($this->confirm('Create a super admin user now?', false)) {
            $this->createSuperAdmin();
        }

        $this->newLine();
        $this->displaySetupInstructions();
        $this->displayCompletionMessage();

        return Command::SUCCESS;
    }

    protected function displayBanner(): void
    {
        $this->line('');
        $this->line('  ⚡ <fg=cyan>╔════════════════════════════════════════╗</>');
        $this->line('  ⚡ <fg=cyan>║                                        ║</>');
        $this->line('  ⚡ <fg=cyan>║         THUNDER PACK  v1.0             ║</>');
        $this->line('  ⚡ <fg=cyan>║   Multi-Tenant SaaS Foundation         ║</>');
        $this->line('  ⚡ <fg=cyan>║                                        ║</>');
        $this->line('  ⚡ <fg=cyan>╚════════════════════════════════════════╝</>');
        $this->line('');
    }

    protected function publishMigrations(): void
    {
        $this->comment('📦 Publishing migrations...');
        
        $params = ['--tag' => 'thunder-pack-migrations', '--provider' => 'ThunderPack\\ThunderPackServiceProvider'];
        
        if ($this->option('force')) {
            $params['--force'] = true;
        }

        Artisan::call('vendor:publish', $params);
        
        $this->info('✓ Migrations published successfully');
        $this->newLine();
    }

    protected function publishConfig(): void
    {
        $this->comment('📦 Publishing configuration...');
        
        $params = ['--tag' => 'thunder-pack-config', '--provider' => 'ThunderPack\\ThunderPackServiceProvider'];
        
        if ($this->option('force')) {
            $params['--force'] = true;
        }

        Artisan::call('vendor:publish', $params);
        
        $this->info('✓ Config file published to config/thunder-pack.php');
        $this->newLine();
    }

    protected function publishViews(): void
    {
        $this->comment('📦 Publishing views...');
        
        $params = ['--tag' => 'thunder-pack-views', '--provider' => 'ThunderPack\\ThunderPackServiceProvider'];
        
        if ($this->option('force')) {
            $params['--force'] = true;
        }

        Artisan::call('vendor:publish', $params);
        
        $this->info('✓ Views published to resources/views/vendor/thunder-pack');
        $this->newLine();
    }

    protected function runMigrations(): void
    {
        $this->comment('🔄 Running migrations...');
        
        Artisan::call('migrate', [], $this->output);
        
        $this->info('✓ Migrations completed');
        $this->newLine();
    }

    protected function seedPlans(): void
    {
        $this->comment('🌱 Seeding subscription plans...');
        
        try {
            Artisan::call('db:seed', [
                '--class' => 'ThunderPack\\Database\\Seeders\\PlanSeeder'
            ], $this->output);
            
            $this->info('✓ Default plans created (Free, Starter, Professional, Enterprise)');
        } catch (\Exception $e) {
            $this->error('✗ Failed to seed plans: ' . $e->getMessage());
            $this->comment('  You can run this manually later with: php artisan db:seed --class=ThunderPack\\Database\\Seeders\\PlanSeeder');
        }
        
        $this->newLine();
    }

    protected function createSuperAdmin(): void
    {
        $this->comment('👤 Creating super admin user...');
        $this->newLine();

        $name = $this->ask('Full name');
        $email = $this->ask('Email address');
        $password = $this->secret('Password (minimum 8 characters)');

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters');
            return;
        }

        $userClass = config('thunder-pack.models.user', \App\Models\User::class);

        try {
            $user = $userClass::create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt($password),
                'email_verified_at' => now(),
                'is_super_admin' => true,
            ]);

            $this->info('✓ Super admin created successfully');
            $this->line("  Email: {$email}");
        } catch (\Exception $e) {
            $this->error('✗ Failed to create super admin: ' . $e->getMessage());
        }

        $this->newLine();
    }

    protected function displaySetupInstructions(): void
    {
        $this->comment('📋 SETUP INSTRUCTIONS:');
        $this->line('');
        
        $this->line('1. Add the <fg=yellow>HasTenants</> trait to your User model:');
        $this->line('');
        $this->line('   <fg=gray>// app/Models/User.php</>');
        $this->line('   use ThunderPack\Traits\HasTenants;');
        $this->line('');
        $this->line('   class User extends Authenticatable {');
        $this->line('       use HasTenants;');
        $this->line('       ...');
        $this->line('   }');
        $this->line('');

        $this->line('2. Configure your environment variables:');
        $this->line('');
        $this->line('   <fg=gray># Optional: WhatsApp Integration</>');
        $this->line('   WHATSAPP_ENABLED=false');
        $this->line('   WHATSAPP_API_URL=');
        $this->line('   WHATSAPP_API_KEY=');
        $this->line('');

        $this->line('3. Update your routes (if needed):');
        $this->line('');
        $this->line('   <fg=gray>// Apply middleware to tenant routes</>');
        $this->line("   Route::middleware(['auth', 'tenant', 'subscription'])->group(...)");
        $this->line('');
        $this->line('   <fg=gray>// Apply middleware to super admin routes</>');
        $this->line("   Route::middleware(['auth', 'superadmin'])->prefix('sa')->group(...)");
        $this->line('');
    }

    protected function displayCompletionMessage(): void
    {
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('');
        $this->info('  ✨ Thunder Pack installation completed successfully!');
        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        $this->comment('📚 Next Steps:');
        $this->line('');
        $this->line('  • Review config/thunder-pack.php for customization options');
        $this->line('  • Check database/migrations for Thunder Pack tables');
        $this->line('  • Visit /sa/dashboard as super admin to manage tenants');
        $this->line('  • Visit /tenant/select to switch between tenants');
        $this->line('');
        
        $this->comment('🔧 Useful Commands:');
        $this->line('');
        $this->line('  php artisan limits:check-staff       - Check tenant limits');
        $this->line('  php artisan whatsapp:test            - Test WhatsApp integration');
        $this->line('');

        $this->comment('📖 Documentation:');
        $this->line('');
        $this->line('  Check vendor/thunder-pack/docs/ for detailed documentation');
        $this->line('');
    }
}

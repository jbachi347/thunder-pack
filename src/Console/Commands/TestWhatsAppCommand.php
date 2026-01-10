<?php

namespace ThunderPack\Console\Commands;

use ThunderPack\Models\TenantWhatsappPhone;
use ThunderPack\Services\WhatsAppService;
use Illuminate\Console\Command;

class TestWhatsAppCommand extends Command
{
    protected $signature = 'whatsapp:test {phone_id?}';
    protected $description = 'Test WhatsApp message sending';

    public function handle()
    {
        $phoneId = $this->argument('phone_id') ?? TenantWhatsappPhone::first()?->id;
        
        if (!$phoneId) {
            $this->error('No phone found');
            return 1;
        }

        $phone = TenantWhatsappPhone::find($phoneId);
        
        if (!$phone) {
            $this->error("Phone with ID {$phoneId} not found");
            return 1;
        }

        $this->info("Testing phone: {$phone->phone_number}");
        $this->info("Original: {$phone->phone_number}");
        $this->info("Formatted: {$phone->formatted_phone}");
        $this->info("Display: {$phone->display_phone}");
        $this->info("Instance: " . ($phone->instance_name ?: 'null/default'));
        $this->newLine();

        $whatsappService = app(WhatsAppService::class);
        
        $this->info("WhatsApp Config:");
        $status = $whatsappService->getStatus();
        $this->table(['Key', 'Value'], [
            ['Enabled', $status['enabled'] ? 'Yes' : 'No'],
            ['Configured', $status['configured'] ? 'Yes' : 'No'],
            ['API URL', $status['api_url']],
            ['API Key', $status['api_key']],
        ]);
        $this->newLine();

        if (!$whatsappService->isConfigured()) {
            $this->error('WhatsApp service not configured!');
            return 1;
        }

        $this->info('Sending test message...');
        
        $message = "🧪 Test from command\n\nDate: " . now()->format('Y-m-d H:i:s');
        
        $result = $whatsappService->sendTestMessage($phone, $message);
        
        $this->newLine();
        $this->info('Result:');
        $this->line(json_encode($result, JSON_PRETTY_PRINT));
        
        if ($result['success']) {
            $this->info('✅ Message sent successfully!');
            return 0;
        } else {
            $this->error('❌ Failed to send message: ' . $result['message']);
            return 1;
        }
    }
}

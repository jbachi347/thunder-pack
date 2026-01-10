<?php

namespace ThunderPack\Jobs;

use ThunderPack\Models\TenantWhatsappPhone;
use ThunderPack\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [60, 180, 600]; // 1 min, 3 min, 10 min (exponential)

    /**
     * The maximum number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public TenantWhatsappPhone $phone,
        public string $message,
        public ?string $notificationType = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsAppService): void
    {
        try {
            // Verify phone is still active before sending
            if (!$this->phone->is_active) {
                Log::warning('Attempted to send WhatsApp to inactive phone', [
                    'phone_id' => $this->phone->id,
                    'tenant_id' => $this->phone->tenant_id,
                ]);
                return;
            }

            $result = $whatsAppService->sendToPhone(
                $this->phone,
                $this->message,
                $this->notificationType
            );

            if (!$result['success']) {
                // If sending failed, throw exception to trigger retry
                throw new \Exception($result['message']);
            }

            Log::info('WhatsApp message sent successfully', [
                'phone_id' => $this->phone->id,
                'tenant_id' => $this->phone->tenant_id,
                'notification_type' => $this->notificationType,
            ]);

        } catch (\Exception $e) {
            Log::error('WhatsApp job failed', [
                'phone_id' => $this->phone->id,
                'tenant_id' => $this->phone->tenant_id,
                'notification_type' => $this->notificationType,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Exception $exception): void
    {
        Log::error('WhatsApp job permanently failed after all retries', [
            'phone_id' => $this->phone->id,
            'tenant_id' => $this->phone->tenant_id,
            'notification_type' => $this->notificationType,
            'error' => $exception->getMessage(),
        ]);
    }
}

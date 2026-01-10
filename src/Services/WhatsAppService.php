<?php

namespace ThunderPack\Services;

use ThunderPack\Models\Tenant;
use ThunderPack\Models\TenantWhatsappPhone;
use ThunderPack\Models\WhatsappMessageLog;
use ThunderPack\Jobs\SendWhatsAppNotificationJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Exception;

class WhatsAppService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected bool $enabled;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.url', '');
        $this->apiKey = config('services.whatsapp.key', '');
        $this->enabled = config('services.whatsapp.enabled', false);
    }

    /**
     * Send a WhatsApp notification to a tenant
     */
    public function sendNotification(
        Tenant $tenant,
        string $notificationType,
        string $message,
        bool $queue = true
    ): array {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'WhatsApp service not configured',
                'sent_count' => 0,
            ];
        }

        $phones = $this->getActivePhonesForNotification($tenant, $notificationType);

        if ($phones->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No active phones configured for this notification type',
                'sent_count' => 0,
            ];
        }

        $results = [];
        foreach ($phones as $phone) {
            if ($queue) {
                // Queue the message for async sending
                SendWhatsAppNotificationJob::dispatch($phone, $message, $notificationType);
                $results[] = ['queued' => true, 'phone' => $phone->display_phone];
            } else {
                // Send immediately
                $result = $this->sendToPhone($phone, $message, $notificationType);
                $results[] = $result;
            }
        }

        return [
            'success' => true,
            'message' => $queue ? 'Messages queued for sending' : 'Messages sent',
            'sent_count' => count($results),
            'results' => $results,
        ];
    }

    /**
     * Send a test message immediately (not queued)
     */
    public function sendTestMessage(
        TenantWhatsappPhone $phone,
        string $message
    ): array {
        if (!$this->isConfigured()) {
            throw new Exception('WhatsApp service not configured');
        }

        if (!$phone->is_active) {
            throw new Exception('Phone is not active');
        }

        return $this->sendToPhone($phone, $message, 'test');
    }

    /**
     * Send message to a specific phone
     */
    public function sendToPhone(
        TenantWhatsappPhone $phone,
        string $message,
        ?string $notificationType = null
    ): array {
        // Create log entry
        $log = WhatsappMessageLog::create([
            'tenant_id' => $phone->tenant_id,
            'tenant_whatsapp_phone_id' => $phone->id,
            'phone_number' => $phone->phone_number,
            'message' => $message,
            'status' => 'pending',
            'notification_type' => $notificationType,
        ]);

        try {
            $instanceName = $phone->instance_name ?: config('services.whatsapp.default_instance', 'default');
            $url = $this->apiUrl . '/message/sendText/' . $instanceName;
            
            Log::info('Sending WhatsApp message', [
                'url' => $url,
                'phone' => $phone->formatted_phone,
                'instance' => $instanceName,
            ]);

            $response = Http::timeout(30)
                ->withHeaders([
                    'apikey' => $this->apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'number' => $phone->formatted_phone,
                    'text' => $message,
                ]);

            $statusCode = $response->status();
            $responseBody = $response->body();
            
            Log::info('WhatsApp API Response', [
                'status_code' => $statusCode,
                'body' => $responseBody,
            ]);

            if ($response->successful()) {
                $responseData = $response->json() ?? [];
                $log->markAsSent($responseData);

                return [
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'phone' => $phone->display_phone,
                    'log_id' => $log->id,
                    'response' => $responseData,
                ];
            } else {
                $responseData = $response->json() ?? [];
                $errorMessage = $responseData['message'] ?? $responseData['error'] ?? "HTTP {$statusCode}: {$responseBody}";
                $log->markAsFailed($errorMessage);

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'phone' => $phone->display_phone,
                    'log_id' => $log->id,
                    'status_code' => $statusCode,
                    'response_body' => $responseBody,
                ];
            }
        } catch (Exception $e) {
            Log::error('WhatsApp API Error: ' . $e->getMessage(), [
                'phone_id' => $phone->id,
                'tenant_id' => $phone->tenant_id,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            $log->markAsError($e->getMessage());

            return [
                'success' => false,
                'message' => 'Error connecting to WhatsApp API: ' . $e->getMessage(),
                'phone' => $phone->display_phone,
                'log_id' => $log->id,
            ];
        }
    }

    /**
     * Get active phones for a specific notification type
     */
    protected function getActivePhonesForNotification(
        Tenant $tenant,
        string $notificationType
    ): Collection {
        return $tenant->whatsappPhones()
            ->active()
            ->get()
            ->filter(fn($phone) => $phone->hasNotificationType($notificationType));
    }

    /**
     * Validate phone number format (E.164)
     */
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        // Remove spaces and special characters except + and digits
        $cleaned = preg_replace('/[^0-9+]/', '', $phoneNumber);

        // Check E.164 format: +[country code][number] (8-15 digits total)
        return preg_match('/^\+?[1-9]\d{7,14}$/', $cleaned) === 1;
    }

    /**
     * Format phone number to standard format
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Add + prefix if not present
        if (!str_starts_with($phoneNumber, '+')) {
            return '+' . $cleaned;
        }

        return '+' . $cleaned;
    }

    /**
     * Get message history for a tenant
     */
    public function getMessageHistory(Tenant $tenant, int $limit = 50): Collection
    {
        return WhatsappMessageLog::where('tenant_id', $tenant->id)
            ->with('whatsappPhone')
            ->recent()
            ->limit($limit)
            ->get();
    }

    /**
     * Get statistics for a tenant
     */
    public function getStatistics(Tenant $tenant): array
    {
        $total = WhatsappMessageLog::where('tenant_id', $tenant->id)->count();
        $sent = WhatsappMessageLog::where('tenant_id', $tenant->id)->sent()->count();
        $failed = WhatsappMessageLog::where('tenant_id', $tenant->id)->failed()->count();
        $pending = WhatsappMessageLog::where('tenant_id', $tenant->id)->pending()->count();

        return [
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $pending,
            'success_rate' => $total > 0 ? round(($sent / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Check if service is properly configured
     */
    public function isConfigured(): bool
    {
        return $this->enabled &&
               !empty($this->apiUrl) &&
               !empty($this->apiKey);
    }

    /**
     * Get service status information
     */
    public function getStatus(): array
    {
        return [
            'enabled' => $this->enabled,
            'configured' => $this->isConfigured(),
            'api_url' => $this->apiUrl ? 'Set' : 'Not set',
            'api_key' => $this->apiKey ? 'Set' : 'Not set',
        ];
    }

    /**
     * Available notification types
     */
    public static function notificationTypes(): array
    {
        return [
            'subscription_activated' => 'Suscripción Activada',
            'subscription_expiring' => 'Suscripción Por Expirar',
            'subscription_expired' => 'Suscripción Expirada',
            'payment_received' => 'Pago Recibido',
            'staff_limit_reached' => 'Límite de Personal Alcanzado',
        ];
    }
}

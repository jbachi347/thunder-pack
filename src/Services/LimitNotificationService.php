<?php

namespace ThunderPack\Services;

use ThunderPack\Models\Tenant;
use ThunderPack\Models\SubscriptionNotification;
use ThunderPack\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class LimitNotificationService
{
    /**
     * Check and notify if staff limit thresholds are reached
     */
    public static function checkAndNotifyStaffLimit(Tenant $tenant): void
    {
        $percentage = $tenant->getStaffUsagePercentage();
        $currentCount = $tenant->getCurrentStaffCount();
        $limit = $tenant->getStaffLimit();
        $remaining = $tenant->getRemainingStaffSlots();

        // Determine threshold reached
        $threshold = null;
        if ($percentage >= 100) {
            $threshold = 100;
        } elseif ($percentage >= 90) {
            $threshold = 90;
        } elseif ($percentage >= 80) {
            $threshold = 80;
        }

        if (!$threshold) {
            return; // No threshold reached
        }

        // Get active subscription
        $subscription = $tenant->activeSubscription;
        if (!$subscription) {
            return; // No active subscription
        }

        // Check if we already sent notification for this threshold
        $existingNotification = SubscriptionNotification::where('subscription_id', $subscription->id)
            ->where('type', "staff_limit_warning_{$threshold}")
            ->where('sent_at', '>=', now()->subDays(7)) // Don't spam - one per week
            ->exists();

        if ($existingNotification) {
            return; // Already notified recently
        }

        // Create notification record
        $notification = SubscriptionNotification::create([
            'subscription_id' => $subscription->id,
            'type' => "staff_limit_warning_{$threshold}",
            'sent_at' => now(),
        ]);

        // Send email notification
        try {
            self::sendEmailNotification($tenant, $percentage, $currentCount, $limit, $remaining, $threshold);
        } catch (\Exception $e) {
            Log::error('Failed to send staff limit email notification', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Send WhatsApp notification
        try {
            self::sendWhatsAppNotification($tenant, $percentage, $currentCount, $limit, $remaining, $threshold);
        } catch (\Exception $e) {
            Log::error('Failed to send staff limit WhatsApp notification', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate staff limit warning message
     */
    protected static function generateStaffLimitMessage(
        float $percentage,
        int $currentCount,
        int $limit,
        int $remaining
    ): string {
        if ($percentage >= 100) {
            return "Has alcanzado el límite máximo de miembros de tu plan ({$currentCount}/{$limit}). "
                . "No podrás agregar más miembros hasta que actualices tu plan o elimines miembros existentes.";
        } elseif ($percentage >= 90) {
            return "Estás muy cerca del límite de miembros de tu plan ({$currentCount}/{$limit}). "
                . "Te quedan solo {$remaining} " . ($remaining === 1 ? 'espacio' : 'espacios') . ". "
                . "Considera actualizar tu plan pronto.";
        } else { // >= 80
            return "Has alcanzado el {$percentage}% del límite de miembros de tu plan ({$currentCount}/{$limit}). "
                . "Te quedan {$remaining} " . ($remaining === 1 ? 'espacio' : 'espacios') . ". "
                . "Considera actualizar tu plan si necesitas agregar más miembros.";
        }
    }

    /**
     * Send email notification
     */
    protected static function sendEmailNotification(
        Tenant $tenant,
        float $percentage,
        int $currentCount,
        int $limit,
        int $remaining,
        int $threshold
    ): void {
        // Get tenant owners/admins emails
        $emails = $tenant->admins()->pluck('email')->toArray();

        if (empty($emails)) {
            return;
        }

        $message = self::generateStaffLimitMessage($percentage, $currentCount, $limit, $remaining);

        // TODO: Implement email sending using Mail facade
        // For now, just log it
        Log::info('Staff limit email notification would be sent', [
            'tenant_id' => $tenant->id,
            'emails' => $emails,
            'threshold' => $threshold,
            'message' => $message,
        ]);

        // Example implementation:
        // foreach ($emails as $email) {
        //     Mail::to($email)->send(new StaffLimitWarningMail($tenant, $percentage, $currentCount, $limit, $remaining));
        // }
    }

    /**
     * Send WhatsApp notification
     */
    protected static function sendWhatsAppNotification(
        Tenant $tenant,
        float $percentage,
        int $currentCount,
        int $limit,
        int $remaining,
        int $threshold
    ): void {
        $whatsappService = app(WhatsAppService::class);

        // Check if WhatsApp notifications are enabled
        if (!config('services.whatsapp_evolution.enabled', false)) {
            return;
        }

        // Format message for WhatsApp
        $message = self::formatWhatsAppMessage($percentage, $currentCount, $limit, $remaining);

        // Send notification
        $whatsappService->sendNotification(
            $tenant,
            'staff_limit_reached',
            $message,
            true // Queue it
        );
    }

    /**
     * Format WhatsApp message with emoji and formatting
     */
    protected static function formatWhatsAppMessage(
        float $percentage,
        int $currentCount,
        int $limit,
        int $remaining
    ): string {
        $emoji = $percentage >= 100 ? '🚫' : ($percentage >= 90 ? '⚠️' : '📊');

        $message = "{$emoji} *Límite de Personal al " . number_format($percentage, 0) . "%*\n\n";

        if ($percentage >= 100) {
            $message .= "❌ *Límite Alcanzado*\n\n";
            $message .= "Has alcanzado el límite máximo de miembros.\n\n";
        } elseif ($percentage >= 90) {
            $message .= "⚠️ *Muy Cerca del Límite*\n\n";
            $message .= "Estás muy cerca del límite de miembros.\n\n";
        } else {
            $message .= "📊 *Acercándote al Límite*\n\n";
        }

        $message .= "👥 Equipo: *{$currentCount}/{$limit}* miembros\n";
        $message .= "📊 Uso: *" . number_format($percentage, 1) . "%*\n";
        
        if ($remaining > 0) {
            $message .= "✅ Restantes: *{$remaining}* " . ($remaining === 1 ? 'espacio' : 'espacios') . "\n\n";
        } else {
            $message .= "🚫 Sin espacios disponibles\n\n";
        }

        if ($percentage >= 90) {
            $message .= "💡 *Recomendación:* Actualiza tu plan para agregar más miembros.";
        } else {
            $message .= "💡 *Tip:* Considera actualizar tu plan si necesitas más miembros.";
        }

        return $message;
    }

    /**
     * Check all active tenants and notify if needed (for scheduled command)
     */
    public static function checkAllTenants(): int
    {
        $notifiedCount = 0;

        Tenant::whereHas('subscriptions', function ($query) {
            $query->where('status', 'active')
                ->orWhere('status', 'trialing');
        })->each(function (Tenant $tenant) use (&$notifiedCount) {
            $percentage = $tenant->getStaffUsagePercentage();

            // Only check tenants at 90%+ to avoid spam
            if ($percentage >= 90) {
                self::checkAndNotifyStaffLimit($tenant);
                $notifiedCount++;
            }
        });

        return $notifiedCount;
    }
}

<?php

namespace ThunderPack\Services;

use ThunderPack\Models\PaymentEvent;
use ThunderPack\Models\Plan;
use ThunderPack\Models\Subscription;
use ThunderPack\Models\SubscriptionNotification;
use ThunderPack\Models\Tenant;
use ThunderPack\Mail\SubscriptionActivated;
use ThunderPack\Services\Gateways\PaymentGatewayInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Exception;

class SubscriptionService
{
    /**
     * Días de anticipación para notificación de expiración
     */
    const EXPIRING_THRESHOLD_DAYS = 7;

    /**
     * Get payment gateway by provider name
     */
    public function getGateway(string $provider): PaymentGatewayInterface
    {
        return match ($provider) {
            'manual' => app(\ThunderPack\Services\Gateways\ManualGateway::class),
            'lemon_squeezy' => app(\ThunderPack\Services\Gateways\LemonSqueezyGateway::class),
            default => throw new Exception("Unsupported payment provider: {$provider}"),
        };
    }

    /**
     * Create a checkout URL for a plan
     * 
     * @param Tenant $tenant
     * @param Plan $plan
     * @param string $provider Provider name (manual, lemon_squeezy, etc.)
     * @param string $billingCycle Billing cycle (monthly, yearly)
     * @return string Checkout URL
     */
    public function createCheckout(Tenant $tenant, Plan $plan, string $provider = 'lemon_squeezy', string $billingCycle = 'monthly'): string
    {
        $gateway = $this->getGateway($provider);
        return $gateway->createCheckoutUrl($plan, $tenant, $billingCycle);
    }

    /**
     * Activar suscripción manualmente
     */
    public function activateManual(Tenant $tenant, Plan $plan, int $days = 30, bool $isTrial = false): Subscription
    {
        // Buscar suscripción existente o crear nueva
        $subscription = $tenant->subscriptions()->latest()->first();

        if ($subscription) {
            // Actualizar existente
            $subscription->update([
                'plan_id' => $plan->id,
                'status' => 'active',
                'provider' => 'manual',
                'trial_ends_at' => $isTrial ? now()->addDays($days) : null,
                'ends_at' => $isTrial ? null : now()->addDays($days),
                'next_billing_date' => now()->addDays($days), // Siempre establecer próxima facturación
            ]);
        } else {
            // Crear nueva
            $subscription = $tenant->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => 'active',
                'provider' => 'manual',
                'trial_ends_at' => $isTrial ? now()->addDays($days) : null,
                'ends_at' => $isTrial ? null : now()->addDays($days),
                'next_billing_date' => now()->addDays($days), // Siempre establecer próxima facturación
            ]);
        }

        // Eliminar notificaciones previas para evitar duplicados
        $this->clearNotifications($subscription);

        // Enviar email de activación
        $this->sendActivationEmail($tenant, $subscription);

        return $subscription;
    }

    /**
     * Renovar suscripción manualmente
     */
    public function renewManual(Tenant $tenant, int $days = 30): Subscription
    {
        $subscription = $tenant->subscriptions()->latest()->firstOrFail();

        // Calcular nueva fecha de finalización
        $currentEndsAt = $subscription->ends_at ? Carbon::parse($subscription->ends_at) : null;

        if ($currentEndsAt && $currentEndsAt->isFuture()) {
            // Si aún no ha vencido → sumar días desde ends_at
            $newEndsAt = $currentEndsAt->addDays($days);
        } else {
            // Si ya venció → sumar días desde now()
            $newEndsAt = now()->addDays($days);
        }

        // Actualizar suscripción
        $subscription->update([
            'status' => 'active',
            'ends_at' => $newEndsAt,
            'trial_ends_at' => null,
        ]);

        // Eliminar notificaciones previas para nuevo periodo
        $this->clearNotifications($subscription);

        // Enviar email de renovación
        $this->sendActivationEmail($tenant, $subscription);

        return $subscription;
    }

    /**
     * Marcar suscripción como vencida
     */
    public function setPastDue(Tenant $tenant): Subscription
    {
        $subscription = $tenant->subscriptions()->latest()->firstOrFail();

        $subscription->update([
            'status' => 'past_due',
        ]);

        return $subscription;
    }

    /**
     * Cancelar suscripción
     */
    public function cancel(Tenant $tenant, bool $immediately = false): Subscription
    {
        $subscription = $tenant->subscriptions()->latest()->firstOrFail();

        $subscription->update([
            'status' => 'canceled',
            'ends_at' => $immediately ? now() : $subscription->ends_at,
        ]);

        return $subscription;
    }

    /**
     * Cambiar plan de suscripción
     */
    public function changePlan(Tenant $tenant, Plan $newPlan, bool $keepCurrentPeriod = true): Subscription
    {
        $subscription = $tenant->subscriptions()->latest()->firstOrFail();

        $updateData = [
            'plan_id' => $newPlan->id,
        ];

        // Si no se mantiene el periodo actual, extender 30 días más
        if (!$keepCurrentPeriod) {
            $updateData['ends_at'] = now()->addDays(30);
        }

        $subscription->update($updateData);

        return $subscription;
    }

    /**
     * Registrar pago manual
     */
    public function recordManualPayment(
        Tenant $tenant,
        int $amountCents,
        string $currency = 'USD',
        string $eventType = 'manual.payment',
        array $metadata = []
    ): PaymentEvent {
        return PaymentEvent::create([
            'tenant_id' => $tenant->id,
            'provider' => 'manual',
            'event_type' => $eventType,
            'provider_event_id' => 'manual_' . now()->timestamp . '_' . $tenant->id,
            'amount_cents' => $amountCents,
            'currency' => $currency,
            'status' => 'success',
            'payload' => array_merge([
                'recorded_by' => Auth::user()?->name ?? 'System',
                'recorded_at' => now()->toISOString(),
            ], $metadata),
        ]);
    }

    /**
     * Eliminar notificaciones previas (para nuevo periodo)
     */
    public function clearNotifications(Subscription $subscription): void
    {
        SubscriptionNotification::where('subscription_id', $subscription->id)->delete();
    }

    /**
     * Enviar email de activación/renovación
     */
    public function sendActivationEmail(Tenant $tenant, Subscription $subscription): void
    {
        $owner = $tenant->users()->wherePivot('is_owner', true)->first();

        if ($owner && $owner->email) {
            Mail::to($owner->email)->send(new SubscriptionActivated($tenant, $subscription));
        }

        // Send WhatsApp notification if configured
        $this->sendWhatsAppNotification($tenant, 'subscription_activated', $subscription);
    }

    /**
     * Enviar notificación de suscripción por expirar
     */
    public function notifySubscriptionExpiring(Tenant $tenant): void
    {
        $subscription = $tenant->latestSubscription();

        if (!$subscription || !$subscription->ends_at) {
            return;
        }

        $daysUntilExpiration = now()->diffInDays($subscription->ends_at, false);

        if ($daysUntilExpiration <= self::EXPIRING_THRESHOLD_DAYS && $daysUntilExpiration > 0) {
            // Check if notification already sent
            $alreadySent = SubscriptionNotification::where('subscription_id', $subscription->id)
                ->where('type', 'expiring')
                ->exists();

            if (!$alreadySent) {
                $message = "⚠️ *Suscripción por vencer*\n\n"
                    . "Tu suscripción de *{$subscription->plan->name}* vencerá en *{$daysUntilExpiration} días*.\n\n"
                    . "📅 Fecha de vencimiento: {$subscription->ends_at->format('d/m/Y')}\n\n"
                    . "Por favor, renueva tu suscripción para continuar usando el servicio.";

                $this->sendWhatsAppNotification($tenant, 'subscription_expiring', $subscription, $message);

                // Record notification sent
                SubscriptionNotification::create([
                    'subscription_id' => $subscription->id,
                    'type' => 'expiring',
                    'sent_at' => now(),
                ]);
            }
        }
    }

    /**
     * Enviar notificación de suscripción expirada
     */
    public function notifySubscriptionExpired(Tenant $tenant): void
    {
        $subscription = $tenant->latestSubscription();

        if (!$subscription || !$subscription->ends_at) {
            return;
        }

        if ($subscription->ends_at->isPast() && $subscription->status !== 'canceled') {
            // Check if notification already sent
            $alreadySent = SubscriptionNotification::where('subscription_id', $subscription->id)
                ->where('type', 'expired')
                ->exists();

            if (!$alreadySent) {
                $message = "❌ *Suscripción expirada*\n\n"
                    . "Tu suscripción de *{$subscription->plan->name}* ha expirado.\n\n"
                    . "📅 Fecha de vencimiento: {$subscription->ends_at->format('d/m/Y')}\n\n"
                    . "Renueva tu suscripción para recuperar el acceso al servicio.";

                $this->sendWhatsAppNotification($tenant, 'subscription_expired', $subscription, $message);

                // Record notification sent
                SubscriptionNotification::create([
                    'subscription_id' => $subscription->id,
                    'type' => 'expired',
                    'sent_at' => now(),
                ]);
            }
        }
    }

    /**
     * Enviar notificación de pago recibido por WhatsApp
     */
    public function notifyPaymentReceived(Tenant $tenant, PaymentEvent $payment): void
    {
        $message = "✅ *Pago recibido*\n\n"
            . "Hemos recibido tu pago correctamente.\n\n"
            . "💰 Monto: " . number_format($payment->amount_cents / 100, 2) . " {$payment->currency}\n"
            . "📅 Fecha: " . now()->format('d/m/Y H:i') . "\n\n"
            . "Gracias por tu pago.";

        $this->sendWhatsAppNotification($tenant, 'payment_received', null, $message);
    }

    /**
     * Enviar notificación por WhatsApp
     */
    protected function sendWhatsAppNotification(
        Tenant $tenant,
        string $notificationType,
        ?Subscription $subscription = null,
        ?string $customMessage = null
    ): void {
        try {
            // Check if WhatsApp service is available
            if (!class_exists(\ThunderPack\Services\WhatsAppService::class)) {
                return;
            }

            $whatsappService = app(\ThunderPack\Services\WhatsAppService::class);

            if (!$whatsappService->isConfigured()) {
                return;
            }

            // Generate message if not provided
            if (!$customMessage && $subscription) {
                $customMessage = $this->generateSubscriptionMessage($tenant, $subscription, $notificationType);
            }

            if ($customMessage) {
                $whatsappService->sendNotification($tenant, $notificationType, $customMessage);
            }
        } catch (Exception $e) {
            // Log error but don't fail the operation
            \Illuminate\Support\Facades\Log::warning('Failed to send WhatsApp notification', [
                'tenant_id' => $tenant->id,
                'notification_type' => $notificationType,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate subscription message
     */
    protected function generateSubscriptionMessage(Tenant $tenant, Subscription $subscription, string $type): string
    {
        // Determine the end date (either trial_ends_at or ends_at)
        $endDate = $subscription->trial_ends_at ?? $subscription->ends_at;
        $dateFormatted = $endDate ? $endDate->format('d/m/Y') : 'No definida';
        $subscriptionType = $subscription->trial_ends_at ? 'periodo de prueba' : 'suscripción';
        
        return match ($type) {
            'subscription_activated' => "✅ *" . ucfirst($subscriptionType) . " activada*\n\n"
                . "Tu {$subscriptionType} de *{$subscription->plan->name}* ha sido activada exitosamente.\n\n"
                . "📅 Válida hasta: {$dateFormatted}\n\n"
                . "¡Gracias por confiar en nosotros!",
            
            default => "Notificación de suscripción: {$type}",
        };
    }

    /**
     * Obtener owner del tenant
     */
    public function getTenantOwner(Tenant $tenant)
    {
        return $tenant->users()->wherePivot('is_owner', true)->first();
    }
}

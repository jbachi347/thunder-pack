<?php

namespace ThunderPack\Services\Gateways;

use ThunderPack\Models\Plan;
use ThunderPack\Models\Subscription;
use ThunderPack\Models\Tenant;
use ThunderPack\Services\SubscriptionService;
use Carbon\Carbon;

/**
 * Manual Gateway - Handles manual subscription activations
 * This gateway encapsulates the existing manual activation logic
 */
class ManualGateway implements PaymentGatewayInterface
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Manual gateway doesn't support checkout URLs
     * Subscriptions are activated directly via admin panel
     */
    public function createCheckoutUrl(Plan $plan, Tenant $tenant, string $billingCycle): string
    {
        throw new \Exception('Manual gateway does not support checkout URLs. Use activateManual() instead.');
    }

    /**
     * Manual gateway doesn't have a customer portal
     */
    public function getCustomerPortalUrl(string $providerCustomerId): ?string
    {
        return null;
    }

    /**
     * Cancel a manual subscription (set status to canceled)
     */
    public function cancelSubscription(string $providerSubscriptionId): bool
    {
        // For manual subscriptions, we just update the status
        // The $providerSubscriptionId is actually the local subscription ID
        $subscription = Subscription::find($providerSubscriptionId);
        
        if (!$subscription) {
            return false;
        }

        $subscription->update([
            'status' => 'canceled',
        ]);

        return true;
    }

    /**
     * Manual gateway doesn't receive webhooks
     */
    public function handleWebhook(array $payload): void
    {
        throw new \Exception('Manual gateway does not support webhooks.');
    }

    /**
     * Sync subscription - for manual gateway, this is a no-op
     */
    public function syncSubscription(Subscription $subscription): void
    {
        // Manual subscriptions don't need syncing with external provider
        // Data is already local
    }

    /**
     * Get provider name
     */
    public function getProviderName(): string
    {
        return 'manual';
    }

    /**
     * Activate a manual subscription
     * This method is specific to ManualGateway and used by SubscriptionService
     *
     * @param Tenant $tenant
     * @param Plan $plan
     * @param int $days Number of days for subscription
     * @param bool $isTrial Whether this is a trial period
     * @return Subscription
     */
    public function activateManual(Tenant $tenant, Plan $plan, int $days = 30, bool $isTrial = false): Subscription
    {
        // Find existing subscription or create new
        $subscription = $tenant->subscriptions()->latest()->first();

        if ($subscription) {
            // Update existing
            $subscription->update([
                'plan_id' => $plan->id,
                'status' => 'active',
                'provider' => 'manual',
                'trial_ends_at' => $isTrial ? now()->addDays($days) : null,
                'ends_at' => $isTrial ? null : now()->addDays($days),
                'next_billing_date' => now()->addDays($days),
                'billing_cycle' => 'monthly', // Default for manual
            ]);
        } else {
            // Create new
            $subscription = $tenant->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => 'active',
                'provider' => 'manual',
                'trial_ends_at' => $isTrial ? now()->addDays($days) : null,
                'ends_at' => $isTrial ? null : now()->addDays($days),
                'next_billing_date' => now()->addDays($days),
                'billing_cycle' => 'monthly', // Default for manual
            ]);
        }

        // Clear previous notifications
        $this->subscriptionService->clearNotifications($subscription);

        // Send activation email
        $this->subscriptionService->sendActivationEmail($tenant, $subscription);

        return $subscription;
    }

    /**
     * Renew a manual subscription
     *
     * @param Tenant $tenant
     * @param int $days Number of days to extend
     * @return Subscription
     */
    public function renewManual(Tenant $tenant, int $days = 30): Subscription
    {
        $subscription = $tenant->subscriptions()->latest()->firstOrFail();

        // Calculate new end date
        $currentEndsAt = $subscription->ends_at ? Carbon::parse($subscription->ends_at) : null;

        if ($currentEndsAt && $currentEndsAt->isFuture()) {
            // Not expired yet → add days from ends_at
            $newEndsAt = $currentEndsAt->addDays($days);
        } else {
            // Already expired → add days from now
            $newEndsAt = now()->addDays($days);
        }

        // Update subscription
        $subscription->update([
            'status' => 'active',
            'ends_at' => $newEndsAt,
            'trial_ends_at' => null,
            'next_billing_date' => $newEndsAt,
        ]);

        // Clear previous notifications for new period
        $this->subscriptionService->clearNotifications($subscription);

        // Send renewal email
        $this->subscriptionService->sendActivationEmail($tenant, $subscription);

        return $subscription;
    }
}

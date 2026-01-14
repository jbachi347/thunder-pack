<?php

namespace ThunderPack\Services\Gateways;

use ThunderPack\Models\Plan;
use ThunderPack\Models\Subscription;
use ThunderPack\Models\Tenant;

interface PaymentGatewayInterface
{
    /**
     * Generate a checkout URL for a given plan and tenant
     *
     * @param Plan $plan The plan to subscribe to
     * @param Tenant $tenant The tenant subscribing
     * @param string $billingCycle 'monthly' or 'yearly'
     * @return string Checkout URL to redirect user
     */
    public function createCheckoutUrl(Plan $plan, Tenant $tenant, string $billingCycle): string;

    /**
     * Get the customer portal URL for managing subscription
     *
     * @param string $providerCustomerId The provider's customer ID
     * @return string|null Customer portal URL or null if not supported
     */
    public function getCustomerPortalUrl(string $providerCustomerId): ?string;

    /**
     * Cancel a subscription with the payment provider
     *
     * @param string $providerSubscriptionId The provider's subscription ID
     * @return bool Success status
     */
    public function cancelSubscription(string $providerSubscriptionId): bool;

    /**
     * Handle an incoming webhook from the payment provider
     *
     * @param array $payload The webhook payload
     * @return void
     */
    public function handleWebhook(array $payload): void;

    /**
     * Sync subscription data from provider
     *
     * @param Subscription $subscription
     * @return void
     */
    public function syncSubscription(Subscription $subscription): void;

    /**
     * Get the provider name
     *
     * @return string Provider identifier (e.g., 'manual', 'lemon_squeezy', 'stripe')
     */
    public function getProviderName(): string;
}

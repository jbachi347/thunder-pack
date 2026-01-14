<?php

namespace ThunderPack\Services\Gateways;

use ThunderPack\Models\PaymentEvent;
use ThunderPack\Models\Plan;
use ThunderPack\Models\Subscription;
use ThunderPack\Models\Tenant;
use ThunderPack\Services\SubscriptionService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

/**
 * Lemon Squeezy Gateway - Handles subscriptions via Lemon Squeezy API
 * 
 * This implementation uses direct API calls instead of the official package
 * due to Laravel 12 compatibility issues.
 * 
 * API Documentation: https://docs.lemonsqueezy.com/api
 */
class LemonSqueezyGateway implements PaymentGatewayInterface
{
    protected string $apiKey;
    protected string $storeId;
    protected string $signingSecret;
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
        $this->apiKey = config('thunder-pack.lemon_squeezy.api_key');
        $this->storeId = config('thunder-pack.lemon_squeezy.store_id');
        $this->signingSecret = config('thunder-pack.lemon_squeezy.signing_secret', '');
    }

    /**
     * Create a checkout URL for a plan
     */
    public function createCheckoutUrl(Plan $plan, Tenant $tenant, string $billingCycle): string
    {
        // Get the appropriate variant ID based on billing cycle
        $variantId = $billingCycle === 'yearly' 
            ? $plan->lemon_yearly_variant_id 
            : $plan->lemon_monthly_variant_id;

        if (!$variantId) {
            throw new Exception("Plan {$plan->code} does not have a Lemon Squeezy variant ID for {$billingCycle} billing");
        }

        // Create checkout via Lemon Squeezy API
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ])->post('https://api.lemonsqueezy.com/v1/checkouts', [
            'data' => [
                'type' => 'checkouts',
                'attributes' => [
                    'checkout_data' => [
                        'custom' => [
                            'tenant_id' => (string) $tenant->id,
                            'plan_id' => (string) $plan->id,
                            'billing_cycle' => $billingCycle,
                        ],
                    ],
                ],
                'relationships' => [
                    'store' => [
                        'data' => [
                            'type' => 'stores',
                            'id' => $this->storeId,
                        ],
                    ],
                    'variant' => [
                        'data' => [
                            'type' => 'variants',
                            'id' => $variantId,
                        ],
                    ],
                ],
            ],
        ]);

        if ($response->failed()) {
            Log::error('Lemon Squeezy checkout creation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception('Failed to create Lemon Squeezy checkout: ' . $response->body());
        }

        $data = $response->json();
        return $data['data']['attributes']['url'] ?? throw new Exception('Checkout URL not found in response');
    }

    /**
     * Get customer portal URL
     */
    public function getCustomerPortalUrl(string $providerCustomerId): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Accept' => 'application/vnd.api+json',
            ])->get("https://api.lemonsqueezy.com/v1/customers/{$providerCustomerId}");

            if ($response->successful()) {
                $data = $response->json();
                return $data['data']['attributes']['urls']['customer_portal'] ?? null;
            }

            return null;
        } catch (Exception $e) {
            Log::error('Failed to get Lemon Squeezy customer portal URL', [
                'customer_id' => $providerCustomerId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(string $providerSubscriptionId): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Accept' => 'application/vnd.api+json',
                'Content-Type' => 'application/vnd.api+json',
            ])->delete("https://api.lemonsqueezy.com/v1/subscriptions/{$providerSubscriptionId}");

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Failed to cancel Lemon Squeezy subscription', [
                'subscription_id' => $providerSubscriptionId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Handle incoming webhook
     */
    public function handleWebhook(array $payload): void
    {
        $eventName = $payload['meta']['event_name'] ?? null;

        if (!$eventName) {
            Log::warning('Lemon Squeezy webhook received without event_name');
            return;
        }

        Log::info("Processing Lemon Squeezy webhook: {$eventName}");

        // Dispatch to appropriate handler
        match ($eventName) {
            'subscription_created' => $this->handleSubscriptionCreated($payload),
            'subscription_updated' => $this->handleSubscriptionUpdated($payload),
            'subscription_cancelled' => $this->handleSubscriptionCancelled($payload),
            'subscription_resumed' => $this->handleSubscriptionResumed($payload),
            'subscription_expired' => $this->handleSubscriptionExpired($payload),
            'subscription_paused' => $this->handleSubscriptionPaused($payload),
            'subscription_unpaused' => $this->handleSubscriptionUnpaused($payload),
            'subscription_payment_success' => $this->handlePaymentSuccess($payload),
            'subscription_payment_failed' => $this->handlePaymentFailed($payload),
            'subscription_payment_recovered' => $this->handlePaymentRecovered($payload),
            'order_created' => $this->handleOrderCreated($payload),
            'order_refunded' => $this->handleOrderRefunded($payload),
            default => Log::info("Unhandled Lemon Squeezy event: {$eventName}"),
        };
    }

    /**
     * Sync subscription from Lemon Squeezy
     */
    public function syncSubscription(Subscription $subscription): void
    {
        if (!$subscription->provider_subscription_id) {
            return;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Accept' => 'application/vnd.api+json',
            ])->get("https://api.lemonsqueezy.com/v1/subscriptions/{$subscription->provider_subscription_id}");

            if ($response->successful()) {
                $data = $response->json()['data']['attributes'];
                $this->updateSubscriptionFromApi($subscription, $data);
            }
        } catch (Exception $e) {
            Log::error('Failed to sync Lemon Squeezy subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get provider name
     */
    public function getProviderName(): string
    {
        return 'lemon_squeezy';
    }

    // ===== PRIVATE WEBHOOK HANDLERS =====

    private function handleSubscriptionCreated(array $payload): void
    {
        $data = $payload['data']['attributes'];
        $subscriptionId = $payload['data']['id'];
        $customData = $data['first_subscription_item']['variant_id'] ?? [];
        
        // Extract custom data
        $tenantId = $payload['meta']['custom_data']['tenant_id'] ?? null;
        $planId = $payload['meta']['custom_data']['plan_id'] ?? null;
        $billingCycle = $payload['meta']['custom_data']['billing_cycle'] ?? 'monthly';

        if (!$tenantId || !$planId) {
            Log::error('Lemon Squeezy subscription_created missing tenant_id or plan_id', ['payload' => $payload]);
            return;
        }

        $tenant = Tenant::find($tenantId);
        $plan = Plan::find($planId);

        if (!$tenant || !$plan) {
            Log::error('Lemon Squeezy subscription_created: tenant or plan not found', [
                'tenant_id' => $tenantId,
                'plan_id' => $planId,
            ]);
            return;
        }

        // Create or update subscription
        $subscription = $tenant->subscriptions()->updateOrCreate(
            ['provider_subscription_id' => $subscriptionId],
            [
                'plan_id' => $plan->id,
                'status' => $this->mapLemonStatus($data['status']),
                'provider' => 'lemon_squeezy',
                'provider_customer_id' => $data['customer_id'],
                'provider_subscription_id' => $subscriptionId,
                'billing_cycle' => $billingCycle,
                'trial_ends_at' => isset($data['trial_ends_at']) ? Carbon::parse($data['trial_ends_at']) : null,
                'ends_at' => isset($data['renews_at']) ? Carbon::parse($data['renews_at']) : null,
                'next_billing_date' => isset($data['renews_at']) ? Carbon::parse($data['renews_at']) : null,
                'provider_payload' => $data,
            ]
        );

        // Send activation email
        $this->subscriptionService->sendActivationEmail($tenant, $subscription);

        Log::info('Lemon Squeezy subscription created', ['subscription_id' => $subscription->id]);
    }

    private function handleSubscriptionUpdated(array $payload): void
    {
        $data = $payload['data']['attributes'];
        $subscriptionId = $payload['data']['id'];
        $subscription = Subscription::where('provider_subscription_id', $subscriptionId)->first();

        if (!$subscription) {
            Log::warning('Lemon Squeezy subscription_updated: subscription not found', ['id' => $data['id']]);
            return;
        }

        $this->updateSubscriptionFromApi($subscription, $data);

        // Check if plan changed (upgrade/downgrade)
        $newVariantId = $data['first_subscription_item']['variant_id'] ?? null;
        if ($newVariantId) {
            $this->handlePlanChange($subscription, $newVariantId);
        }

        Log::info('Lemon Squeezy subscription updated', ['subscription_id' => $subscription->id]);
    }

    private function handleSubscriptionCancelled(array $payload): void
    {
        $data = $payload['data']['attributes'];
        $subscriptionId = $payload['data']['id'];
        $subscription = Subscription::where('provider_subscription_id', $subscriptionId)->first();

        if (!$subscription) {
            return;
        }

        $subscription->update([
            'status' => 'canceled',
            'ends_at' => isset($data['ends_at']) ? Carbon::parse($data['ends_at']) : now(),
        ]);

        Log::info('Lemon Squeezy subscription cancelled', ['subscription_id' => $subscription->id]);
    }

    private function handleSubscriptionResumed(array $payload): void
    {
        $data = $payload['data']['attributes'];
        $subscriptionId = $payload['data']['id'];
        $subscription = Subscription::where('provider_subscription_id', $subscriptionId)->first();

        if (!$subscription) {
            return;
        }

        $subscription->update([
            'status' => 'active',
            'ends_at' => isset($data['renews_at']) ? Carbon::parse($data['renews_at']) : null,
        ]);

        Log::info('Lemon Squeezy subscription resumed', ['subscription_id' => $subscription->id]);
    }

    private function handleSubscriptionExpired(array $payload): void
    {
        $data = $payload['data']['attributes'];
        $subscriptionId = $payload['data']['id'];
        $subscription = Subscription::where('provider_subscription_id', $subscriptionId)->first();

        if (!$subscription) {
            return;
        }

        $subscription->update([
            'status' => 'canceled',
        ]);

        Log::info('Lemon Squeezy subscription expired', ['subscription_id' => $subscription->id]);
    }

    private function handleSubscriptionPaused(array $payload): void
    {
        $data = $payload['data']['attributes'];
        $subscriptionId = $payload['data']['id'];
        $subscription = Subscription::where('provider_subscription_id', $subscriptionId)->first();

        if (!$subscription) {
            return;
        }

        $subscription->update([
            'status' => 'paused',
        ]);

        Log::info('Lemon Squeezy subscription paused', ['subscription_id' => $subscription->id]);
    }

    private function handleSubscriptionUnpaused(array $payload): void
    {
        $data = $payload['data']['attributes'];
        $subscriptionId = $payload['data']['id'];
        $subscription = Subscription::where('provider_subscription_id', $subscriptionId)->first();

        if (!$subscription) {
            return;
        }

        $subscription->update([
            'status' => 'active',
        ]);

        Log::info('Lemon Squeezy subscription unpaused', ['subscription_id' => $subscription->id]);
    }

    private function handlePaymentSuccess(array $payload): void
    {
        $data = $payload['data']['attributes'];
        $subscriptionId = $payload['data']['id'];
        $subscription = Subscription::where('provider_subscription_id', $subscriptionId)->first();

        if (!$subscription) {
            return;
        }

        // Record payment event
        PaymentEvent::create([
            'tenant_id' => $subscription->tenant_id,
            'provider' => 'lemon_squeezy',
            'event_type' => 'subscription.payment.success',
            'provider_event_id' => $payload['meta']['event_name'] . '_' . $subscriptionId,
            'amount_cents' => isset($data['total']) ? (int) ($data['total'] * 100) : null,
            'currency' => $data['currency'] ?? 'USD',
            'status' => 'success',
            'payload' => $payload,
        ]);

        // Extend subscription
        if (isset($data['renews_at'])) {
            $subscription->update([
                'ends_at' => Carbon::parse($data['renews_at']),
                'next_billing_date' => Carbon::parse($data['renews_at']),
                'status' => 'active',
            ]);
        }

        Log::info('Lemon Squeezy payment success', ['subscription_id' => $subscription->id]);
    }

    private function handlePaymentFailed(array $payload): void
    {
        $data = $payload['data']['attributes'];
        $subscriptionId = $payload['data']['id'];
        $subscription = Subscription::where('provider_subscription_id', $subscriptionId)->first();

        if (!$subscription) {
            return;
        }

        // Record payment event
        PaymentEvent::create([
            'tenant_id' => $subscription->tenant_id,
            'provider' => 'lemon_squeezy',
            'event_type' => 'subscription.payment.failed',
            'provider_event_id' => $payload['meta']['event_name'] . '_' . $subscriptionId,
            'status' => 'failed',
            'payload' => $payload,
        ]);

        // Set to past_due
        $subscription->update([
            'status' => 'past_due',
        ]);

        Log::warning('Lemon Squeezy payment failed', ['subscription_id' => $subscription->id]);
    }

    private function handlePaymentRecovered(array $payload): void
    {
        $data = $payload['data']['attributes'];
        $subscriptionId = $payload['data']['id'];
        $subscription = Subscription::where('provider_subscription_id', $subscriptionId)->first();

        if (!$subscription) {
            return;
        }

        $subscription->update([
            'status' => 'active',
        ]);

        Log::info('Lemon Squeezy payment recovered', ['subscription_id' => $subscription->id]);
    }

    private function handleOrderCreated(array $payload): void
    {
        // Record order in payment events for tracking
        $data = $payload['data']['attributes'];
        $orderId = $payload['data']['id'];
        
        PaymentEvent::create([
            'tenant_id' => $payload['meta']['custom_data']['tenant_id'] ?? null,
            'provider' => 'lemon_squeezy',
            'event_type' => 'order.created',
            'provider_event_id' => $data['identifier'] ?? $orderId,
            'amount_cents' => isset($data['total']) ? (int) ($data['total'] * 100) : null,
            'currency' => $data['currency'] ?? 'USD',
            'status' => $data['status'] === 'paid' ? 'success' : 'pending',
            'payload' => $payload,
        ]);

        Log::info('Lemon Squeezy order created', ['order_id' => $orderId]);
    }

    private function handleOrderRefunded(array $payload): void
    {
        $data = $payload['data']['attributes'];
        $orderId = $payload['data']['id'];
        
        // Record refund in payment events
        PaymentEvent::create([
            'tenant_id' => $payload['meta']['custom_data']['tenant_id'] ?? null,
            'provider' => 'lemon_squeezy',
            'event_type' => 'order.refunded',
            'provider_event_id' => $data['identifier'] ?? $orderId,
            'amount_cents' => isset($data['refunded_amount']) ? (int) ($data['refunded_amount'] * 100) : null,
            'currency' => $data['currency'] ?? 'USD',
            'status' => 'success',
            'payload' => $payload,
        ]);

        Log::info('Lemon Squeezy order refunded', ['order_id' => $orderId]);
    }

    // ===== HELPER METHODS =====

    /**
     * Update subscription from API data
     */
    private function updateSubscriptionFromApi(Subscription $subscription, array $data): void
    {
        $subscription->update([
            'status' => $this->mapLemonStatus($data['status']),
            'trial_ends_at' => isset($data['trial_ends_at']) ? Carbon::parse($data['trial_ends_at']) : null,
            'ends_at' => isset($data['renews_at']) ? Carbon::parse($data['renews_at']) : 
                        (isset($data['ends_at']) ? Carbon::parse($data['ends_at']) : null),
            'next_billing_date' => isset($data['renews_at']) ? Carbon::parse($data['renews_at']) : null,
            'provider_payload' => $data,
        ]);
    }

    /**
     * Map Lemon Squeezy status to our status
     */
    private function mapLemonStatus(string $lemonStatus): string
    {
        return match ($lemonStatus) {
            'on_trial' => 'trialing',
            'active' => 'active',
            'paused' => 'paused',
            'past_due' => 'past_due',
            'unpaid' => 'past_due',
            'cancelled' => 'canceled',
            'expired' => 'canceled',
            default => 'active',
        };
    }

    /**
     * Handle plan changes (upgrades/downgrades)
     */
    private function handlePlanChange(Subscription $subscription, string $newVariantId): void
    {
        // Find plan by variant ID
        $plan = Plan::where('lemon_monthly_variant_id', $newVariantId)
                    ->orWhere('lemon_yearly_variant_id', $newVariantId)
                    ->first();

        if ($plan && $plan->id !== $subscription->plan_id) {
            Log::info('Lemon Squeezy plan change detected', [
                'subscription_id' => $subscription->id,
                'old_plan_id' => $subscription->plan_id,
                'new_plan_id' => $plan->id,
            ]);

            $subscription->update([
                'plan_id' => $plan->id,
            ]);

            // Determine billing cycle from variant
            if ($newVariantId === $plan->lemon_yearly_variant_id) {
                $subscription->update(['billing_cycle' => 'yearly']);
            } elseif ($newVariantId === $plan->lemon_monthly_variant_id) {
                $subscription->update(['billing_cycle' => 'monthly']);
            }
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (empty($this->signingSecret)) {
            // If no signing secret configured, skip verification (not recommended for production)
            return true;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $this->signingSecret);
        
        return hash_equals($expectedSignature, $signature);
    }
}

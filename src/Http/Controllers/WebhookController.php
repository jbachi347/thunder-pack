<?php

namespace ThunderPack\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use ThunderPack\Services\Gateways\LemonSqueezyGateway;
use ThunderPack\Models\LemonSqueezyWebhook;

class WebhookController
{
    /**
     * Handle Lemon Squeezy webhooks
     */
    public function lemonSqueezy(Request $request, LemonSqueezyGateway $gateway): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Signature');
        
        // Log for debugging
        Log::info('Webhook received', [
            'has_signature' => !empty($signature),
            'signature_preview' => $signature ? substr($signature, 0, 20) . '...' : 'null',
        ]);

        if (!$signature) {
            Log::error('Webhook signature missing');
            return response('Signature header missing', 401);
        }

        // Verify webhook signature
        if (!$gateway->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Lemon Squeezy webhook signature verification failed');
            return response('Invalid signature', 401);
        }

        try {
            $data = json_decode($payload, true);

            if (!$data) {
                Log::error('Lemon Squeezy webhook invalid JSON');
                return response('Invalid JSON', 400);
            }

            // Store webhook in database for debugging
            LemonSqueezyWebhook::create([
                'event_name' => $data['meta']['event_name'] ?? 'unknown',
                'event_id' => $data['data']['id'] ?? null,
                'signature' => substr($signature, 0, 50),
                'payload' => $data,
                'received_at' => now(),
            ]);

            // Delegate to gateway
            $gateway->handleWebhook($data);

            return response('Webhook handled', 200);
        } catch (\Exception $e) {
            Log::error('Lemon Squeezy webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return 200 to avoid Lemon Squeezy retries for invalid data
            return response('Webhook received', 200);
        }
    }
}

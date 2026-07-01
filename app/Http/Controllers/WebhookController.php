<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\EscrowEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WebhookController extends Controller
{
    protected EscrowEngine $escrowEngine;

    public function __construct(EscrowEngine $escrowEngine)
    {
        $this->escrowEngine = $escrowEngine;
    }

    /**
     * Handle incoming Nomba Webhook
     */
    public function handle(Request $request)
    {
        Log::info('Nomba Webhook Received:', $request->all());

        // Validate webhook signature if secret is configured
        $secret = config('services.nomba.webhook_secret');
        if ($secret) {
            $signature = $request->header('X-Nomba-Signature');
            $computedSignature = hash_hmac('sha256', $request->getContent(), $secret);
            if ($signature !== $computedSignature) {
                Log::warning('Nomba Webhook Signature Mismatch');
                return response()->json(['message' => 'Invalid signature'], 401);
            }
        }

        // Extract event details
        $event = $request->input('event');
        
        // Nomba sends different structures, but we look for payment success
        if ($event === 'payment.success' || $request->input('status') === 'SUCCESS') {
            $orderRef = $request->input('data.orderReference') ?? $request->input('orderReference');
            $paymentRef = $request->input('data.paymentReference') ?? $request->input('paymentReference');

            if (!$orderRef) {
                return response()->json(['message' => 'Missing order reference'], 400);
            }

            $order = Order::where('order_number', $orderRef)->first();

            if (!$order) {
                Log::warning("Nomba Webhook: Order not found for reference: {$orderRef}");
                return response()->json(['message' => 'Order not found'], 404);
            }

            if ($order->payment_status === 'paid') {
                return response()->json(['message' => 'Order already processed'], 200);
            }

            // Process payment based on payment method
            if ($order->payment_method === 'escrow') {
                $this->escrowEngine->holdPayment($order);
                $order->update(['nomba_payment_reference' => $paymentRef]);
                Log::info("Escrow payment initialized for Order: {$order->order_number}");
            } else {
                // Standard payment: Mark as paid immediately
                $order->update([
                    'payment_status' => 'paid',
                    'shipping_status' => 'delivered', // Standard payments can bypass escrow shipping flows
                    'nomba_payment_reference' => $paymentRef
                ]);
                Log::info("Standard payment completed for Order: {$order->order_number}");
            }

            return response()->json(['message' => 'Webhook processed successfully'], 200);
        }

        return response()->json(['message' => 'Event ignored'], 200);
    }

    /**
     * Helper to simulate a Nomba checkout payment redirect for the demo
     */
    public function demoCheckoutView(Request $request)
    {
        $reference = $request->query('reference');
        $order = Order::where('order_number', $reference)->firstOrFail();

        return view('demo.checkout', compact('order'));
    }

    /**
     * Trigger a mock webhook call to test the integration locally or on staging
     */
    public function triggerDemoWebhook(Request $request)
    {
        $orderNumber = $request->input('order_number');
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        // Construct a payload mimicking Nomba's payment.success event
        $payload = [
            'event' => 'payment.success',
            'status' => 'SUCCESS',
            'data' => [
                'orderReference' => $order->order_number,
                'paymentReference' => 'demo_nomba_ref_' . rand(100000, 999999),
                'amount' => (float) $order->total_amount,
                'currency' => 'NGN',
                'paymentMethod' => 'CARD',
            ]
        ];

        // Send internal post request to the webhook handler
        // Using Laravel's Route class or Http client
        $response = Http::post(route('webhooks.nomba'), $payload);

        if ($response->successful()) {
            return redirect()->route('orders.track', ['orderNumber' => $order->order_number])
                ->with('success', 'Demo Payment Simulated! The webhook was triggered successfully.');
        }

        return back()->with('error', 'Failed to simulate webhook payment: ' . $response->body());
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\NombaService;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StorefrontController extends Controller
{
    protected NombaService $nombaService;

    public function __construct(NombaService $nombaService)
    {
        $this->nombaService = $nombaService;
    }

    /**
     * Display the public storefront
     */
    public function showStore($slug)
    {
        $store = Store::where('slug', $slug)->firstOrFail();
        $products = $store->products()->where('is_active', true)->get();

        return view('storefront.store', compact('store', 'products'));
    }

    /**
     * Show the checkout page for a product
     */
    public function checkout(Request $request, $slug)
    {
        $store = Store::where('slug', $slug)->firstOrFail();
        
        $productId = $request->query('product_id');
        $quantity = (int) $request->query('quantity', 1);
        
        $product = Product::where('store_id', $store->id)
            ->where('id', $productId)
            ->where('is_active', true)
            ->firstOrFail();

        $totalAmount = $product->price * $quantity;

        return view('storefront.checkout', compact('store', 'product', 'quantity', 'totalAmount'));
    }

    public function placeOrder(StoreOrderRequest $request, $slug)
    {
        $store = Store::where('slug', $slug)->firstOrFail();

        // Validation is handled by StoreOrderRequest


        $product = Product::where('store_id', $store->id)
            ->where('id', $request->product_id)
            ->firstOrFail();

        $totalAmount = $product->price * $request->quantity;

        return DB::transaction(function () use ($request, $store, $product, $totalAmount) {
            // 1. Create or find Customer
            $customer = Customer::firstOrCreate(
                [
                    'store_id' => $store->id,
                    'phone' => $request->customer_phone,
                ],
                [
                    'name' => $request->customer_name,
                    'email' => $request->customer_email,
                ]
            );

            // 2. Create Order
            $orderNumber = 'NCS-' . strtoupper(Str::random(10));
            $order = Order::create([
                'store_id' => $store->id,
                'customer_id' => $customer->id,
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
                'payment_status' => 'pending',
                'payment_method' => $request->payment_method,
                'shipping_status' => 'pending',
                'shipping_address' => $request->shipping_address,
                'customer_notes' => $request->customer_notes,
            ]);

            // 3. Create Order Item
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->price,
            ]);

            // 4. Call Nomba Service to create checkout
            $checkoutResult = $this->nombaService->createCheckout([
                'amount' => $totalAmount,
                'customerEmail' => $customer->email,
                'customerId' => 'cust_' . $customer->id,
                'orderReference' => $order->order_number,
                'callbackUrl' => route('webhooks.nomba'),
            ]);

            if ($checkoutResult['success']) {
                $order->update([
                    'nomba_order_id' => $checkoutResult['data']['orderReference'],
                ]);

                // Redirect to the Nomba checkout page
                return redirect($checkoutResult['data']['checkoutUrl']);
            }

            Log::error('Failed to create Nomba checkout: ' . json_encode($checkoutResult));
            return back()->with('error', 'Unable to initiate payment with Nomba. Please try again. Error: ' . ($checkoutResult['message'] ?? ''));
        });
    }

    /**
     * Track order and manage escrow confirmation
     */
    public function trackOrder($orderNumber)
    {
        $order = Order::with(['store', 'customer', 'items.product', 'escrow.events'])->where('order_number', $orderNumber)->firstOrFail();

        return view('storefront.track', compact('order'));
    }

    /**
     * Buyer confirms receipt of goods, releasing escrow funds
     */
    public function confirmReceipt(Request $request, $orderNumber)
    {
        $request->validate([
            'release_code' => 'required|string|size:6',
        ]);

        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        
        if (!$order->escrow) {
            return back()->with('error', 'No escrow associated with this order.');
        }

        $escrowEngine = app(\App\Services\EscrowEngine::class);
        $result = $escrowEngine->releaseFunds($order->escrow, $request->release_code);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Buyer reports a dispute
     */
    public function disputeOrder(Request $request, $orderNumber)
    {
        $request->validate([
            'dispute_reason' => 'required|string|min:10',
        ]);

        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        if (!$order->escrow) {
            return back()->with('error', 'No escrow associated with this order.');
        }

        $escrowEngine = app(\App\Services\EscrowEngine::class);
        $success = $escrowEngine->disputeEscrow($order->escrow, $request->dispute_reason);

        if ($success) {
            return back()->with('success', 'Dispute raised successfully. Nomba Commerce Shield admin will review.');
        }

        return back()->with('error', 'Could not dispute this order. Please check its status.');
    }
}

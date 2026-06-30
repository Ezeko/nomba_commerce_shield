<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Order;
use App\Models\Customer;
use App\Services\NombaService;
use App\Services\EscrowEngine;
use App\Http\Requests\StoreStoreRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected NombaService $nombaService;
    protected EscrowEngine $escrowEngine;

    public function __construct(NombaService $nombaService, EscrowEngine $escrowEngine)
    {
        $this->nombaService = $nombaService;
        $this->escrowEngine = $escrowEngine;
    }

    /**
     * Display the merchant dashboard overview
     */
    public function index()
    {
        $user = Auth::user();
        $store = $user->store;

        // If no store exists, redirect to creation
        if (!$store) {
            return redirect()->route('merchant.store.create');
        }

        // 1. Calculate Analytics
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();

        // Revenue Today: Completed orders paid today (standard + released escrow)
        $revenueToday = Order::where('store_id', $store->id)
            ->where('payment_status', 'paid')
            ->whereDate('updated_at', $today)
            ->where(function ($query) {
                $query->where('payment_method', 'standard')
                      ->orWhereHas('escrow', function ($q) {
                          $q->where('status', 'released');
                      });
            })
            ->sum('total_amount');

        // Revenue This Week
        $revenueThisWeek = Order::where('store_id', $store->id)
            ->where('payment_status', 'paid')
            ->where('updated_at', '>=', $startOfWeek)
            ->where(function ($query) {
                $query->where('payment_method', 'standard')
                      ->orWhereHas('escrow', function ($q) {
                          $q->where('status', 'released');
                      });
            })
            ->sum('total_amount');

        // Funds Locked in Escrow (held, shipped, delivered, disputed)
        $escrowFundsLocked = Order::where('store_id', $store->id)
            ->whereHas('escrow', function ($q) {
                $q->whereIn('status', ['held', 'shipped', 'delivered', 'disputed']);
            })
            ->sum('total_amount');

        // Completed Orders count
        $completedOrdersCount = Order::where('store_id', $store->id)
            ->where('payment_status', 'paid')
            ->where(function ($query) {
                $query->where('payment_method', 'standard')
                      ->orWhereHas('escrow', function ($q) {
                          $q->where('status', 'released');
                      });
            })
            ->count();

        // Repeat Customers: Customers with more than 1 order
        $repeatCustomersCount = Customer::where('store_id', $store->id)
            ->has('orders', '>', 1)
            ->count();

        // Recent Orders
        $recentOrders = Order::with(['customer', 'escrow'])
            ->where('store_id', $store->id)
            ->latest()
            ->take(5)
            ->get();

        return view('merchant.dashboard', compact(
            'store',
            'revenueToday',
            'revenueThisWeek',
            'escrowFundsLocked',
            'completedOrdersCount',
            'repeatCustomersCount',
            'recentOrders'
        ));
    }

    /**
     * Show store creation form
     */
    public function createStore()
    {
        if (Auth::user()->store) {
            return redirect()->route('merchant.dashboard');
        }

        return view('merchant.store.create');
    }

    /**
     * Store a new merchant store
     */
    public function storeStore(StoreStoreRequest $request)
    {
        $user = Auth::user();
        if ($user->store) {
            return redirect()->route('merchant.dashboard');
        }

        // Validation is automatically handled by StoreStoreRequest


        // Automatically generate a Nomba Virtual Account for this store
        $virtualAccountResult = $this->nombaService->createVirtualAccount(
            $request->name,
            $request->slug
        );

        $virtualAccountNumber = null;
        $virtualAccountBank = null;
        $virtualAccountName = null;

        if ($virtualAccountResult['success']) {
            $virtualAccountNumber = $virtualAccountResult['data']['accountNumber'];
            $virtualAccountBank = $virtualAccountResult['data']['bankName'];
            $virtualAccountName = $virtualAccountResult['data']['accountName'];
        }

        Store::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'slug' => Str::slug($request->slug),
            'description' => $request->description,
            'virtual_account_number' => $virtualAccountNumber,
            'virtual_account_bank' => $virtualAccountBank,
            'virtual_account_name' => $virtualAccountName,
            'trust_score' => 100, // Starts at 100
        ]);

        return redirect()->route('merchant.dashboard')
            ->with('success', 'Store created successfully! Your Nomba Virtual Account has been provisioned.');
    }

    /**
     * View all store orders
     */
    public function orders()
    {
        $store = Auth::user()->store;
        $orders = Order::with(['customer', 'escrow'])
            ->where('store_id', $store->id)
            ->latest()
            ->paginate(10);

        return view('merchant.orders.index', compact('store', 'orders'));
    }

    /**
     * Show a single order details
     */
    public function showOrder(Order $order)
    {
        $store = Auth::user()->store;
        if ($order->store_id !== $store->id) {
            abort(403);
        }

        $order->load(['customer', 'items.product', 'escrow.events']);
        return view('merchant.orders.show', compact('store', 'order'));
    }

    /**
     * Update shipping status for escrow orders
     */
    public function updateShippingStatus(Request $request, Order $order)
    {
        $store = Auth::user()->store;
        if ($order->store_id !== $store->id) {
            abort(403);
        }

        $request->validate([
            'shipping_status' => 'required|in:shipped,delivered',
        ]);

        if ($order->payment_method !== 'escrow' || !$order->escrow) {
            return back()->with('error', 'Shipping status updates are only available for escrow-protected orders.');
        }

        $success = false;
        if ($request->shipping_status === 'shipped') {
            $success = $this->escrowEngine->markAsShipped($order->escrow);
        } elseif ($request->shipping_status === 'delivered') {
            $success = $this->escrowEngine->markAsDelivered($order->escrow);
        }

        if ($success) {
            return back()->with('success', 'Shipping status updated successfully.');
        }

        return back()->with('error', 'Failed to update shipping status. Please check the current escrow state.');
    }
}

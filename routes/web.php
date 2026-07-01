<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\Merchant\DashboardController;
use App\Http\Controllers\Merchant\ProductController;
use App\Http\Controllers\Merchant\WithdrawalController;
use Illuminate\Support\Facades\Route;

// Public Landing Page
Route::get('/', function () {
    return view('welcome');
});

// Nomba Webhook (Excluded from CSRF in bootstrap/app.php)
Route::post('/webhooks/nomba', [WebhookController::class, 'handle'])->name('webhooks.nomba');

// Demo & Simulation Routes
Route::get('/demo/checkout', [WebhookController::class, 'demoCheckoutView'])->name('demo.nomba.checkout');
Route::post('/demo/webhook/trigger', [WebhookController::class, 'triggerDemoWebhook'])->name('demo.webhook.trigger');

// Public Storefront Routes
Route::get('/store/{slug}', [StorefrontController::class, 'showStore'])->name('storefront.store');
Route::get('/store/{slug}/checkout', [StorefrontController::class, 'checkout'])->name('storefront.checkout');
Route::post('/store/{slug}/order', [StorefrontController::class, 'placeOrder'])->name('storefront.order');
Route::get('/orders/{orderNumber}/track', [StorefrontController::class, 'trackOrder'])->name('orders.track');
Route::post('/orders/{orderNumber}/confirm', [StorefrontController::class, 'confirmReceipt'])->name('orders.confirm');
Route::post('/orders/{orderNumber}/dispute', [StorefrontController::class, 'disputeOrder'])->name('orders.dispute');

// Redirect default dashboard to merchant dashboard
Route::get('/dashboard', function () {
    return redirect()->route('merchant.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Merchant Dashboard Routes
Route::middleware(['auth', 'verified'])->prefix('merchant')->name('merchant.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/store/create', [DashboardController::class, 'createStore'])->name('store.create');
    Route::post('/store', [DashboardController::class, 'storeStore'])->name('store.store');
    Route::get('/orders', [DashboardController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order}', [DashboardController::class, 'showOrder'])->name('orders.show');
    Route::post('/orders/{order}/shipping', [DashboardController::class, 'updateShippingStatus'])->name('orders.shipping');
    
    // Product Management CRUD
    Route::resource('products', ProductController::class);

    // Withdrawals
    Route::get('/withdraw', [WithdrawalController::class, 'create'])->name('withdraw.create');
    Route::post('/withdraw', [WithdrawalController::class, 'store'])->name('withdraw.store');
});

// Profile Management
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

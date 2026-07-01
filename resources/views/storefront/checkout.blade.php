<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Checkout - {{ $store->name }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body {
                font-family: 'Outfit', sans-serif;
            }
        </style>
    </head>
    <body class="antialiased bg-slate-950 text-slate-100 min-h-screen pb-16">
        <header class="border-b border-slate-900 bg-slate-950/85 backdrop-blur-md sticky top-0 z-40">
            <div class="max-w-4xl mx-auto px-6 h-20 flex items-center justify-between">
                <a href="{{ route('storefront.store', $store->slug) }}" class="text-sm text-slate-400 hover:text-white">← Return to Store</a>
                <span class="font-extrabold tracking-tight text-lg">Secure Checkout</span>
                <div class="w-16"></div> <!-- Spacer -->
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-6 py-12">
            
            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-5 gap-8 items-start">
                <!-- Left Column: Checkout Form (3 cols) -->
                <div class="md:col-span-3 bg-slate-900/55 border border-slate-850 p-8 rounded-3xl backdrop-blur-sm space-y-6">
                    <h2 class="text-xl font-bold text-white">Billing & Delivery Information</h2>
                    
                    <form method="POST" action="{{ route('storefront.order', $store->slug) }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="{{ $quantity }}">

                        <!-- Customer Name -->
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Full Name</label>
                            <input type="text" name="customer_name" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-100 focus:border-indigo-500 focus:ring-0 text-sm" placeholder="e.g. John Doe">
                        </div>

                        <!-- Contact Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Email -->
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email Address</label>
                                <input type="email" name="customer_email" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-100 focus:border-indigo-500 focus:ring-0 text-sm" placeholder="johndoe@example.com">
                            </div>
                            <!-- Phone -->
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">WhatsApp / Phone Number</label>
                                <input type="text" name="customer_phone" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-100 focus:border-indigo-500 focus:ring-0 text-sm" placeholder="e.g. 08012345678">
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Delivery Address</label>
                            <textarea name="shipping_address" required rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-100 focus:border-indigo-500 focus:ring-0 text-sm" placeholder="Street, City, State"></textarea>
                        </div>

                        <!-- Customer Notes -->
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Notes to Seller (Optional)</label>
                            <input type="text" name="customer_notes" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-100 focus:border-indigo-500 focus:ring-0 text-sm" placeholder="e.g. Please call before delivery">
                        </div>

                        <!-- Payment Method Selection -->
                        <div class="space-y-4 pt-4 border-t border-slate-850">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Choose Protection Level</label>
                            
                            <!-- Standard -->
                            <label class="relative flex p-4 rounded-2xl bg-slate-950 border border-slate-800 cursor-pointer hover:border-slate-750 transition-colors">
                                <input type="radio" name="payment_method" value="standard" class="mt-1 text-indigo-600 focus:ring-0 bg-slate-950 border-slate-800">
                                <div class="ml-3">
                                    <span class="block text-sm font-bold text-white">Standard Payment</span>
                                    <span class="block text-xs text-slate-500 mt-1">Funds go directly to the merchant. Best for low value or trusted merchants.</span>
                                </div>
                            </label>

                            <!-- Escrow -->
                            <label class="relative flex p-4 rounded-2xl bg-slate-950 border-2 border-teal-500/30 cursor-pointer hover:bg-slate-900/40 transition-all">
                                <input type="radio" name="payment_method" value="escrow" checked class="mt-1 text-teal-500 focus:ring-0 bg-slate-950 border-slate-800">
                                <div class="ml-3">
                                    <span class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-white">Protected Payment (Escrow)</span>
                                        <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-teal-400/10 text-teal-400 border border-teal-400/20 uppercase tracking-wider">Secure</span>
                                    </span>
                                    <span class="block text-xs text-slate-400 mt-1">Funds are held safely by Nomba. Releasing only when you receive your package.</span>
                                </div>
                            </label>
                        </div>

                        <button type="submit" class="w-full py-4 bg-gradient-to-r from-indigo-600 to-teal-500 hover:opacity-95 text-slate-950 font-bold rounded-2xl shadow-xl shadow-indigo-600/10 active:scale-95 transition-all text-center block text-sm">
                            Proceed to Payment
                        </button>
                    </form>
                </div>

                <!-- Right Column: Order Summary (2 cols) -->
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-slate-900/40 border border-slate-850 p-6 rounded-3xl backdrop-blur-sm space-y-6">
                        <h3 class="font-bold text-lg text-white">Order Summary</h3>
                        
                        <div class="flex gap-4 items-center">
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-16 h-16 rounded-2xl object-cover border border-slate-800">
                            <div>
                                <h4 class="font-bold text-white text-sm">{{ $product->name }}</h4>
                                <p class="text-xs text-slate-400 mt-0.5">Quantity: {{ $quantity }}</p>
                            </div>
                        </div>

                        <div class="border-t border-slate-850 pt-4 space-y-3 text-sm">
                            <div class="flex justify-between text-slate-400">
                                <span>Subtotal</span>
                                <span>₦{{ number_format($product->price * $quantity, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-slate-400">
                                <span>Delivery Fee</span>
                                <span class="text-xs text-teal-400 font-medium">FREE</span>
                            </div>
                            <div class="flex justify-between items-center text-white font-black text-lg border-t border-slate-850 pt-4">
                                <span>Total</span>
                                <span>₦{{ number_format($totalAmount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>

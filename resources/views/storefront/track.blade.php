<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Track Order - {{ $order->order_number }}</title>
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
                <a href="{{ route('storefront.store', $order->store->slug) }}" class="text-sm text-slate-400 hover:text-white">← Visit Store</a>
                <span class="font-extrabold tracking-tight text-lg">Order Tracking</span>
                <div class="w-16"></div>
            </div>
        </header>

        <main class="max-w-3xl mx-auto px-6 py-12 space-y-8">
            
            <!-- Success/Error Alerts -->
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Order Overview Card -->
            <div class="bg-slate-900/40 border border-slate-850 p-6 rounded-3xl backdrop-blur-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                    <div>
                        <span class="text-xs text-slate-500 uppercase tracking-widest block">Order Number</span>
                        <h1 class="text-2xl font-mono font-black text-white mt-1">{{ $order->order_number }}</h1>
                    </div>
                    <div class="px-4 py-2 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs font-bold text-center">
                        {{ $order->payment_method === 'escrow' ? '🛡️ Escrow Protected' : 'Standard Payment' }}
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 border-t border-slate-850 pt-4 text-sm">
                    <div>
                        <span class="text-xs text-slate-500 block">Total Amount</span>
                        <span class="font-bold text-white text-lg">₦{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 block">Payment Status</span>
                        <span class="font-bold text-white uppercase tracking-wider
                            @if($order->payment_status === 'paid') text-emerald-400
                            @elseif($order->payment_status === 'refunded') text-rose-400
                            @else text-amber-500
                            @endif
                        ">
                            {{ $order->payment_status }}
                        </span>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <span class="text-xs text-slate-500 block">Merchant Shop</span>
                        <span class="font-bold text-slate-300">{{ $order->store->name }}</span>
                    </div>
                </div>
            </div>

            <!-- Escrow Specific Timeline & Controls -->
            @if($order->payment_method === 'escrow' && $order->escrow)
                <div class="bg-slate-900/40 border border-slate-850 p-8 rounded-3xl backdrop-blur-sm space-y-8">
                    <h2 class="text-lg font-bold text-white">Escrow Payment Protection Timeline</h2>

                    <!-- Horizontal Stepper -->
                    <div class="grid grid-cols-4 gap-2 relative">
                        <!-- Line -->
                        <div class="absolute top-4 left-0 right-0 h-0.5 bg-slate-800 pointer-events-none"></div>

                        <!-- Step 1: Paid/Held -->
                        <div class="text-center z-10">
                            <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold text-xs {{ in_array($order->escrow->status, ['held', 'shipped', 'delivered', 'released', 'disputed']) ? 'bg-teal-400 text-slate-950 shadow-lg shadow-teal-400/20' : 'bg-slate-800 text-slate-400' }}">1</div>
                            <span class="text-[10px] sm:text-xs font-semibold text-slate-300 block mt-2">Paid & Held</span>
                        </div>

                        <!-- Step 2: Shipped -->
                        <div class="text-center z-10">
                            <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold text-xs {{ in_array($order->escrow->status, ['shipped', 'delivered', 'released', 'disputed']) ? 'bg-teal-400 text-slate-950 shadow-lg shadow-teal-400/20' : 'bg-slate-800 text-slate-400' }}">2</div>
                            <span class="text-[10px] sm:text-xs font-semibold text-slate-300 block mt-2">Shipped</span>
                        </div>

                        <!-- Step 3: Delivered -->
                        <div class="text-center z-10">
                            <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold text-xs {{ in_array($order->escrow->status, ['delivered', 'released']) ? 'bg-teal-400 text-slate-950 shadow-lg shadow-teal-400/20' : 'bg-slate-800 text-slate-400' }}">3</div>
                            <span class="text-[10px] sm:text-xs font-semibold text-slate-300 block mt-2">Delivered</span>
                        </div>

                        <!-- Step 4: Released -->
                        <div class="text-center z-10">
                            <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold text-xs {{ $order->escrow->status === 'released' ? 'bg-emerald-500 text-slate-950 shadow-lg shadow-emerald-400/20' : 'bg-slate-800 text-slate-400' }}">4</div>
                            <span class="text-[10px] sm:text-xs font-semibold text-slate-300 block mt-2">Settled</span>
                        </div>
                    </div>

                    <!-- Escrow State Alerts / Actions -->
                    @if($order->escrow->status === 'held' || $order->escrow->status === 'shipped' || $order->escrow->status === 'delivered')
                        <div class="p-6 rounded-2xl bg-indigo-950/20 border border-indigo-500/20 space-y-6">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-teal-400/10 text-teal-400 flex items-center justify-center font-mono font-black text-2xl tracking-wider">
                                    {{ $order->escrow->release_code }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-white text-sm">Your Delivery Confirmation PIN</h3>
                                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                                        Give this 6-digit PIN to the delivery rider or vendor **ONLY** when you have received and verified your items.
                                    </p>
                                </div>
                            </div>

                            <!-- Release Form -->
                            <form action="{{ route('orders.confirm', $order->order_number) }}" method="POST" class="space-y-3">
                                @csrf
                                <div class="flex gap-2">
                                    <input type="text" name="release_code" required maxlength="6" class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-100 text-center tracking-widest font-bold text-lg focus:border-indigo-500 focus:ring-0 max-w-[140px]" placeholder="PIN">
                                    <button type="submit" class="flex-1 py-3 bg-teal-400 hover:bg-teal-300 text-slate-950 font-bold rounded-xl text-xs transition-all active:scale-95 shadow-md shadow-teal-500/10">
                                        Release Funds to Seller
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Dispute Trigger -->
                        <div class="pt-4 border-t border-slate-850">
                            <details class="group">
                                <summary class="text-xs text-rose-400 font-bold cursor-pointer hover:underline list-none flex items-center gap-1">
                                    ⚠️ Something wrong with your order? Raise a dispute
                                </summary>
                                <form action="{{ route('orders.dispute', $order->order_number) }}" method="POST" class="mt-4 space-y-3">
                                    @csrf
                                    <textarea name="dispute_reason" required rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-100 focus:border-rose-500 focus:ring-0 text-xs" placeholder="Please explain the issue in detail (e.g. items not delivered, wrong color, damaged)..."></textarea>
                                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-xl text-xs transition-all active:scale-95">
                                        Raise Dispute
                                    </button>
                                </form>
                            </details>
                        </div>
                    @elseif($order->escrow->status === 'disputed')
                        <div class="p-6 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 space-y-3">
                            <h3 class="font-bold text-sm flex items-center gap-2">
                                <span>⚠️ Order Under Dispute</span>
                            </h3>
                            <p class="text-xs leading-relaxed">
                                You raised a dispute for this order. Nomba Commerce Shield arbiters are reviewing the transaction history.
                            </p>
                            <div class="p-3 bg-slate-950/40 rounded-xl border border-rose-500/10 text-xs italic">
                                "{{ $order->escrow->dispute_reason }}"
                            </div>
                        </div>
                    @elseif($order->escrow->status === 'released')
                        <div class="p-6 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-center space-y-2">
                            <h3 class="font-bold text-base">✓ Escrow Released Successfully</h3>
                            <p class="text-xs text-slate-400">
                                The funds have been successfully settled into the merchant's Nomba wallet. Thank you for buying safely!
                            </p>
                        </div>
                    @endif
                </div>
            @else
                <!-- Standard Non-Escrow Tracking -->
                <div class="bg-slate-900/40 border border-slate-850 p-8 rounded-3xl backdrop-blur-sm text-center py-12 space-y-4">
                    <div class="w-16 h-16 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-3xl mx-auto">✓</div>
                    <h2 class="text-xl font-bold text-white">Standard Order Completed</h2>
                    <p class="text-sm text-slate-400 max-w-md mx-auto">
                        Your payment was processed immediately and sent directly to the merchant. The merchant has been notified to ship your item.
                    </p>
                </div>
            @endif

            <!-- Order Items Summary -->
            <div class="bg-slate-900/40 border border-slate-850 p-6 rounded-3xl backdrop-blur-sm space-y-4">
                <h3 class="font-bold text-white">Items Summary</h3>
                @foreach($order->items as $item)
                    <div class="flex justify-between items-center text-sm py-2 border-b border-slate-850 last:border-0">
                        <div class="flex items-center gap-3">
                            <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="w-10 h-10 rounded-lg object-cover">
                            <div>
                                <span class="font-semibold text-slate-300">{{ $item->product->name }}</span>
                                <span class="text-xs text-slate-500 block">Qty: {{ $item->quantity }}</span>
                            </div>
                        </div>
                        <span class="font-bold text-white">₦{{ number_format($item->price * $item->quantity, 2) }}</span>
                    </div>
                @endforeach
            </div>

        </main>
    </body>
</html>

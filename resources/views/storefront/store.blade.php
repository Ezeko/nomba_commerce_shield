<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $store->name }} - Nomba Commerce Shield Store</title>
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
        <!-- Aurora background effect -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-80 bg-gradient-to-b from-indigo-900/10 to-transparent blur-3xl pointer-events-none"></div>

        <!-- Store Header -->
        <header class="border-b border-slate-900 bg-slate-950/85 backdrop-blur-md sticky top-0 z-40">
            <div class="max-w-6xl mx-auto px-6 h-20 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-indigo-600 to-teal-400 flex items-center justify-center font-bold text-sm text-white">
                        {{ substr($store->name, 0, 1) }}
                    </div>
                    <span class="font-extrabold tracking-tight text-lg">{{ $store->name }}</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <!-- Share Button -->
                    <button onclick="openShareModal()" class="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 hover:bg-indigo-500/20 border border-indigo-500/20 text-indigo-400 text-xs font-bold transition-all active:scale-95 shadow-md">
                        <span>🔗</span> Share Store
                    </button>

                    <!-- Trust Score Pill -->
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-teal-400/10 border border-teal-400/20 text-teal-400 text-xs font-bold shadow-lg shadow-teal-500/5">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-400 animate-pulse"></span>
                        Trust Score: {{ $store->trust_score }}
                    </div>
                </div>
            </div>
        </header>

        <!-- Store Hero -->
        <section class="max-w-6xl mx-auto px-6 pt-12 pb-8">
            <div class="p-8 rounded-3xl bg-slate-900/40 border border-slate-850 backdrop-blur-sm flex flex-col md:flex-row justify-between gap-6 items-start">
                <div class="space-y-3 max-w-2xl">
                    <h1 class="text-3xl font-black text-white">{{ $store->name }}</h1>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        {{ $store->description ?? 'Welcome to my store! Tap on any product below to order. All purchases are secured via Nomba Commerce Shield.' }}
                    </p>
                </div>
                
                <!-- Escrow Protection Badge -->
                <div class="p-4 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex gap-3 items-start max-w-sm">
                    <span class="text-lg">🛡️</span>
                    <div>
                        <h4 class="text-xs font-bold text-indigo-300 uppercase">Escrow Protection Available</h4>
                        <p class="text-[10px] text-slate-400 mt-1 leading-relaxed">
                            You can choose "Protected Payment" at checkout. Your funds will be locked securely in escrow until you confirm delivery.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products Grid -->
        <main class="max-w-6xl mx-auto px-6 py-8">
            <h2 class="text-xl font-bold text-white mb-8 flex items-center gap-2">
                <span>Products List</span>
                <span class="h-1 w-10 bg-indigo-500 rounded-full"></span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($products as $product)
                    <div class="group relative rounded-3xl bg-slate-905 border border-slate-900 hover:border-slate-800/80 overflow-hidden flex flex-col justify-between transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-indigo-950/20">
                        <div>
                            <!-- Product Image -->
                            <div class="h-64 overflow-hidden relative bg-slate-900">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </div>

                            <!-- Product Details -->
                            <div class="p-6 space-y-3">
                                <h3 class="font-bold text-lg text-white group-hover:text-indigo-400 transition-colors">{{ $product->name }}</h3>
                                <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">{{ $product->description }}</p>
                            </div>
                        </div>

                        <!-- Product Footer & CTA -->
                        <div class="px-6 pb-6 pt-3 flex items-center justify-between border-t border-slate-900/50">
                            <span class="text-xl font-black text-white">₦{{ number_format($product->price, 2) }}</span>
                            <a href="{{ route('storefront.checkout', ['slug' => $store->slug, 'product_id' => $product->id]) }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-indigo-600/10 active:scale-95">
                                Buy Now
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 text-center text-slate-500 bg-slate-900/20 rounded-3xl border border-dashed border-slate-800">
                        No products are currently available in this store.
                    </div>
                @endforelse
            </div>
        </main>

        <!-- Share Modal -->
        <div id="shareModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300">
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-sm shadow-2xl relative transform scale-95 transition-transform duration-300">
                <button onclick="closeShareModal()" class="absolute top-4 right-4 text-slate-500 hover:text-slate-300 text-xl font-bold active:scale-90">
                    &times;
                </button>
                
                <div class="text-center space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-400 text-xl font-bold mx-auto mb-2">
                        📱
                    </div>
                    <h3 class="text-lg font-black text-white">Scan to Shop</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Scan the QR code below using your phone's camera to browse and pay securely via Nomba.
                    </p>

                    <!-- QR Code -->
                    <div class="p-4 bg-white border border-slate-800 rounded-3xl shadow-lg inline-block mx-auto">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode(route('storefront.store', $store->slug)) }}" alt="Store QR Code" class="w-40 h-40">
                    </div>

                    <!-- Store URL Copy Input -->
                    <div class="space-y-2 mt-4">
                        <div class="flex gap-2">
                            <input type="text" id="shareLinkInput" value="{{ route('storefront.store', $store->slug) }}" class="text-xs bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 w-full text-slate-400 truncate select-all" readonly>
                            <button onclick="copyShareLink()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white text-xs font-bold rounded-xl transition-all shadow-md shrink-0">
                                Copy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function openShareModal() {
                const modal = document.getElementById('shareModal');
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.remove('opacity-0');
                    modal.querySelector('.transform').classList.remove('scale-95');
                }, 10);
            }

            function closeShareModal() {
                const modal = document.getElementById('shareModal');
                modal.classList.add('opacity-0');
                modal.querySelector('.transform').classList.add('scale-95');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            }

            function copyShareLink() {
                const input = document.getElementById('shareLinkInput');
                input.select();
                navigator.clipboard.writeText(input.value);
                alert('Store link copied to clipboard!');
            }
        </script>
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Nomba Commerce Shield - Secure Social Commerce</title>
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
    <body class="antialiased bg-slate-950 text-slate-100 overflow-x-hidden">
        <!-- Aurora glow effects -->
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-600/20 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute top-1/3 right-1/4 w-[400px] h-[400px] bg-teal-500/10 rounded-full blur-[150px] pointer-events-none"></div>

        <!-- Header -->
        <header class="relative border-b border-slate-800/80 backdrop-blur-md bg-slate-950/70 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('logo.png') }}" alt="Nomba Commerce Shield Logo" class="w-10 h-10 rounded-xl object-cover shadow-lg shadow-indigo-500/25">
                    <span class="font-bold text-xl tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white to-slate-300">
                        Nomba Commerce <span class="text-teal-400">Shield</span>
                    </span>
                </div>

                <nav class="flex items-center gap-6">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('merchant.dashboard') }}" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">Merchant Login</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-lg shadow-indigo-600/20 transition-all active:scale-95">
                                    Start Free Store
                                </a>
                            @endif
                        @endauth
                    @endif
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative pt-20 pb-16 px-6">
            <div class="max-w-5xl mx-auto text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 text-xs font-semibold mb-6 animate-pulse">
                    <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                    Nomba Hackathon 2026 Entry
                </div>

                <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight leading-tight mb-8">
                    Trust Infrastructure for <br class="hidden md:inline">
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 via-purple-400 to-teal-300">
                        Social Commerce in Africa
                    </span>
                </h1>

                <p class="text-lg md:text-xl text-slate-400 max-w-3xl mx-auto mb-10 leading-relaxed">
                    Instantly spin up a social store. Offer buyers the choice between 
                    <span class="text-slate-200 font-medium">Standard Checkout</span> and 
                    <span class="text-teal-400 font-semibold">Protected Escrow Payments</span>. 
                    Build creditability with a dynamic, decentralized Trust Score.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-base font-semibold text-slate-950 bg-gradient-to-r from-teal-400 to-emerald-400 hover:opacity-90 rounded-2xl shadow-xl shadow-teal-500/20 transition-all hover:scale-[1.02] active:scale-95">
                        Setup Your Store in 60s
                    </a>
                    <a href="#how-it-works" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-base font-semibold text-slate-300 bg-slate-900 border border-slate-800 hover:bg-slate-800/80 rounded-2xl transition-all">
                        See How It Works
                    </a>
                </div>
            </div>
        </section>

        <!-- Features Grid -->
        <section id="how-it-works" class="py-20 px-6 border-t border-slate-900 bg-slate-950/50">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4">Combating Social Media Scams & Fake Alerts</h2>
                    <p class="text-slate-400 max-w-2xl mx-auto">
                        A typical Nigerian vendor sells on Instagram or WhatsApp, risking fake payment alerts. Buyers risk being scammed. Commerce Shield fixes both.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Card 1 -->
                    <div class="relative group p-8 rounded-3xl bg-slate-900/50 border border-slate-800/80 hover:border-indigo-500/30 transition-all hover:-translate-y-1">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-400 font-bold mb-6 text-xl">
                            01
                        </div>
                        <h3 class="text-xl font-bold mb-3">Nomba Virtual Accounts</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Every store gets a dedicated Nomba Virtual Account. Payments made via transfer are instantly reconciled—no manual screenshots or fake alert risks.
                        </p>
                    </div>

                    <!-- Card 2 -->
                    <div class="relative group p-8 rounded-3xl bg-slate-900/50 border border-slate-800/80 hover:border-teal-500/30 transition-all hover:-translate-y-1">
                        <div class="w-12 h-12 rounded-2xl bg-teal-500/10 flex items-center justify-center text-teal-400 font-bold mb-6 text-xl">
                            02
                        </div>
                        <h3 class="text-xl font-bold mb-3">Protected Payments (Escrow)</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Buyers can lock payment in escrow. Funds are held securely. The seller ships, and once the buyer inputs their confirmation code, funds are instantly released.
                        </p>
                    </div>

                    <!-- Card 3 -->
                    <div class="relative group p-8 rounded-3xl bg-slate-900/50 border border-slate-800/80 hover:border-purple-500/30 transition-all hover:-translate-y-1">
                        <div class="w-12 h-12 rounded-2xl bg-purple-500/10 flex items-center justify-center text-purple-400 font-bold mb-6 text-xl">
                            03
                        </div>
                        <h3 class="text-xl font-bold mb-3">Reputation Trust Score</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            A dynamic 0-100 score shown on the vendor's storefront. Calculated from successful deliveries, ratings, and disputes, giving buyers total confidence.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Dynamic Escrow Demo Preview -->
        <section class="py-20 px-6 bg-gradient-to-b from-slate-950 to-slate-900">
            <div class="max-w-6xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <span class="text-teal-400 font-semibold text-sm uppercase tracking-wider mb-2 block">Built for Hackathon Demonstration</span>
                        <h2 class="text-3xl md:text-4xl font-bold mb-6">Fully Interactive Sandbox Flow</h2>
                        <p class="text-slate-400 mb-6 leading-relaxed">
                            We have built the entire payment lifecycle. Register as a merchant, create a storefront, add products, and test the checkout. 
                        </p>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center gap-3">
                                <span class="w-5 h-5 rounded-full bg-teal-400/20 text-teal-400 flex items-center justify-center text-xs">✓</span>
                                <span class="text-slate-300 text-sm">Simulated Nomba Checkout Redirect page</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="w-5 h-5 rounded-full bg-teal-400/20 text-teal-400 flex items-center justify-center text-xs">✓</span>
                                <span class="text-slate-300 text-sm">One-click webhook simulator to complete payments</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="w-5 h-5 rounded-full bg-teal-400/20 text-teal-400 flex items-center justify-center text-xs">✓</span>
                                <span class="text-slate-300 text-sm">Escrow confirmation via secure 6-digit PIN</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Visual Dashboard Mockup -->
                    <div class="p-6 rounded-3xl bg-slate-950 border border-slate-800 shadow-2xl relative overflow-hidden">
                        <div class="absolute -top-10 -right-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-2xl"></div>
                        <div class="flex items-center justify-between border-b border-slate-800 pb-4 mb-6">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                                <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                            </div>
                            <span class="text-xs text-slate-500">nomba-commerce-shield.sandbox</span>
                        </div>

                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-slate-500">STOREFRONT URL</p>
                                    <p class="text-sm font-semibold text-slate-300">{{ str_replace(['http://', 'https://'], '', url('/store/fashion-hub')) }}</p>
                                </div>
                                <div class="px-3 py-1.5 rounded-full bg-teal-400/10 text-teal-400 border border-teal-400/20 text-xs font-bold">
                                    Trust Score: 98
                                </div>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800/80">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-xs text-slate-400">Locked in Escrow</span>
                                    <span class="text-xs text-teal-400 font-medium">Verification Pending</span>
                                </div>
                                <div class="text-2xl font-bold text-slate-100">₦245,000.00</div>
                                <p class="text-[10px] text-slate-500 mt-2">Awaiting buyer delivery confirmation</p>
                            </div>

                            <!-- Progress Bar -->
                            <div class="space-y-2">
                                <div class="flex justify-between text-xs text-slate-400">
                                    <span>Escrow Lifecycle</span>
                                    <span class="text-indigo-400">Shipped</span>
                                </div>
                                <div class="h-2 w-full bg-slate-900 rounded-full overflow-hidden">
                                    <div class="h-full w-2/3 bg-gradient-to-r from-indigo-500 to-teal-400 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-12 border-t border-slate-900 text-center text-slate-500 text-sm bg-slate-950">
            <p>© 2026 Nomba Commerce Shield. Built for the Nomba Social Commerce Hackathon.</p>
        </footer>
    </body>
</html>

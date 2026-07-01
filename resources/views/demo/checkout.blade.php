<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nomba Checkout Simulator (Sandbox)</title>
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
<body class="bg-slate-900 text-slate-100 flex items-center justify-center min-h-screen p-4">
    <!-- Main checkout box -->
    <div class="w-full max-w-md bg-slate-950 border border-slate-800 rounded-3xl p-8 shadow-2xl space-y-6 relative overflow-hidden">
        <div class="absolute -top-16 -right-16 w-32 h-32 bg-indigo-600/10 rounded-full blur-xl"></div>
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-850 pb-4">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-md bg-gradient-to-tr from-indigo-600 to-teal-400 flex items-center justify-center text-[10px] font-bold text-white">N</div>
                <span class="font-bold text-xs tracking-wider text-slate-300 uppercase">Nomba Checkout <span class="text-teal-400">Sandbox</span></span>
            </div>
            <span class="text-[10px] bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 px-2 py-0.5 rounded-md font-bold uppercase">Demo Mode</span>
        </div>

        <!-- Store & Amount -->
        <div class="text-center py-4 space-y-2">
            <p class="text-xs text-slate-400">Pay to <span class="text-slate-200 font-bold">{{ $order->store->name }}</span></p>
            <h2 class="text-3xl font-black text-white">₦{{ number_format($order->total_amount, 2) }}</h2>
            <div class="inline-block px-3 py-1 bg-slate-900 rounded-xl border border-slate-800 text-xs font-mono text-slate-400">
                Ref: {{ $order->order_number }}
            </div>
        </div>

        <!-- Payment Method Mock Tabs -->
        <div class="grid grid-cols-2 gap-2 text-center text-xs">
            <div class="p-3 bg-slate-900 border-2 border-indigo-500 rounded-xl font-semibold">
                💳 Pay with Card
            </div>
            <div class="p-3 bg-slate-900 border border-slate-850 rounded-xl text-slate-500 font-semibold cursor-not-allowed">
                🏦 Bank Transfer
            </div>
        </div>

        <!-- Mock Card Fields -->
        <div class="space-y-4 pt-2">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Card Number</label>
                <input type="text" disabled value="••••  ••••  ••••  4321" class="w-full bg-slate-900/50 border border-slate-850 rounded-xl px-4 py-3 text-slate-400 text-sm cursor-not-allowed">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Expiry Date</label>
                    <input type="text" disabled value="12 / 29" class="w-full bg-slate-900/50 border border-slate-850 rounded-xl px-4 py-3 text-slate-400 text-sm cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">CVV</label>
                    <input type="text" disabled value="•••" class="w-full bg-slate-900/50 border border-slate-850 rounded-xl px-4 py-3 text-slate-400 text-sm cursor-not-allowed">
                </div>
            </div>
        </div>

        <!-- Simulation CTA Form -->
        <form action="{{ route('demo.webhook.trigger') }}" method="POST" class="pt-4">
            @csrf
            <input type="hidden" name="order_number" value="{{ $order->order_number }}">
            
            <button type="submit" class="w-full py-4 bg-gradient-to-r from-indigo-600 to-teal-500 text-slate-950 font-black rounded-2xl shadow-xl shadow-indigo-600/10 hover:opacity-95 active:scale-98 transition-all text-sm text-center">
                Authorize Sandbox Payment
            </button>
        </form>

        <p class="text-[10px] text-slate-500 text-center leading-relaxed">
            Clicking Authorize simulates a successful payment and triggers the Nomba Webhook endpoint (`POST /webhooks/nomba`) asynchronously.
        </p>
    </div>
</body>
</html>

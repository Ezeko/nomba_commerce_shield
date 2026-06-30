<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Merchant Hub') }} — <span class="text-teal-400 font-bold">{{ $store->name }}</span>
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Store URL: <a href="{{ route('storefront.store', $store->slug) }}" target="_blank" class="text-indigo-400 hover:underline">{{ str_replace(['http://', 'https://'], '', route('storefront.store', $store->slug)) }} ↗</a>
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('merchant.products.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-md transition-all active:scale-95">
                    + Add Product
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Success/Error Alerts -->
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Top Cards Grid (Trust Score + Virtual Account) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Trust Score Card -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Store Trust Score</h3>
                            <span class="text-xs text-indigo-400 bg-indigo-500/10 px-2 py-1 rounded-md">Social Rating</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-5xl font-extrabold text-gray-900 dark:text-white">{{ $store->trust_score }}</span>
                            <span class="text-gray-400 dark:text-gray-500 text-xl">/ 100</span>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="mt-4 h-2.5 w-full bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r {{ $store->trust_score >= 80 ? 'from-teal-500 to-emerald-400' : ($store->trust_score >= 50 ? 'from-amber-500 to-yellow-400' : 'from-rose-500 to-red-600') }}" style="width: {{ $store->trust_score }}%"></div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-4 leading-relaxed">
                        Calculated dynamically from successful escrow deliveries and disputes. Higher scores attract more buyers.
                    </p>
                </div>

                <!-- Nomba Virtual Account Card -->
                <div class="lg:col-span-2 bg-gradient-to-br from-indigo-900 via-indigo-950 to-slate-950 p-6 rounded-2xl shadow-lg border border-indigo-500/20 relative overflow-hidden flex flex-col justify-between text-white">
                    <div class="absolute -top-12 -right-12 w-36 h-36 bg-teal-500/15 rounded-full blur-2xl"></div>
                    
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></div>
                                <span class="text-xs font-bold text-teal-400 uppercase tracking-widest">Nomba Virtual Account</span>
                            </div>
                            <span class="text-[10px] bg-white/10 px-2.5 py-1 rounded-full uppercase tracking-wider">Auto-Reconciled</span>
                        </div>

                        @if($store->virtual_account_number)
                            <div class="space-y-4">
                                <div>
                                    <p class="text-xs text-indigo-200/60 uppercase">Account Number</p>
                                    <p class="text-3xl font-mono font-bold tracking-wider text-slate-100 mt-1">{{ $store->virtual_account_number }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-[10px] text-indigo-200/60 uppercase">Bank Name</p>
                                        <p class="text-sm font-semibold text-slate-200">{{ $store->virtual_account_bank }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-indigo-200/60 uppercase">Account Name</p>
                                        <p class="text-sm font-semibold text-slate-200 truncate">{{ $store->virtual_account_name }}</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="py-4 text-center">
                                <p class="text-sm text-indigo-200">Virtual account provisioning failed. Running in offline mode.</p>
                            </div>
                        @endif
                    </div>
                    
                    <p class="text-[10px] text-indigo-200/40 mt-4">
                        Payments sent to this account are credited instantly. No manual receipt uploads required.
                    </p>
                </div>
            </div>

            <!-- Analytics Statistics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card 1: Wallet Balance -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Wallet Balance</p>
                    <p class="text-2xl font-black text-indigo-500 mt-2">₦{{ number_format($store->balance, 2) }}</p>
                    <p class="text-[10px] text-emerald-500 font-medium mt-1">Available for Payout</p>
                </div>

                <!-- Card 2: Revenue Today -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Revenue Today</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white mt-2">₦{{ number_format($revenueToday, 2) }}</p>
                    <p class="text-[10px] text-gray-500 mt-1">Today's Settled Sales</p>
                </div>

                <!-- Card 3: Escrow Balance -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Escrow Balance</p>
                    <p class="text-2xl font-black text-teal-400 mt-2">₦{{ number_format($escrowFundsLocked, 2) }}</p>
                    <p class="text-[10px] text-amber-500 font-medium mt-1">Locked awaiting delivery</p>
                </div>

                <!-- Card 4: Completed Orders -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Completed Orders</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white mt-2">{{ $completedOrdersCount }}</p>
                    <p class="text-[10px] text-gray-400 mt-1">{{ $repeatCustomersCount }} Repeat Customers</p>
                </div>
            </div>

            <!-- Recent Orders Section -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">Recent Transactions</h3>
                    <a href="{{ route('merchant.orders.index') }}" class="text-xs text-indigo-500 hover:underline">View All Orders →</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/40 text-xs font-bold text-gray-500 uppercase">
                                <th class="px-6 py-4">Order Number</th>
                                <th class="px-6 py-4">Customer</th>
                                <th class="px-6 py-4">Amount</th>
                                <th class="px-6 py-4">Type</th>
                                <th class="px-6 py-4">Payment</th>
                                <th class="px-6 py-4">Escrow Status</th>
                                <th class="px-6 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                            @forelse($recentOrders as $order)
                                <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-900/20">
                                    <td class="px-6 py-4 font-mono font-bold text-gray-900 dark:text-white">{{ $order->order_number }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $order->customer->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $order->customer->phone }}</div>
                                    </td>
                                    <td class="px-6 py-4 font-semibold">₦{{ number_format($order->total_amount, 2) }}</td>
                                    <td class="px-6 py-4">
                                        @if($order->payment_method === 'escrow')
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-teal-400/10 text-teal-400 border border-teal-400/20">Protected</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-gray-400/10 text-gray-400 border border-gray-400/20">Standard</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($order->payment_status === 'paid')
                                            <span class="text-emerald-500 font-bold">Paid</span>
                                        @elseif($order->payment_status === 'refunded')
                                            <span class="text-rose-500 font-bold">Refunded</span>
                                        @else
                                            <span class="text-gray-400 font-medium">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($order->payment_method === 'escrow' && $order->escrow)
                                            <span class="text-xs font-semibold uppercase tracking-wider
                                                @if($order->escrow->status === 'held') text-amber-500
                                                @elseif($order->escrow->status === 'shipped') text-indigo-400
                                                @elseif($order->escrow->status === 'delivered') text-blue-400
                                                @elseif($order->escrow->status === 'released') text-emerald-500
                                                @elseif($order->escrow->status === 'disputed') text-rose-500
                                                @else text-gray-400
                                                @endif
                                            ">
                                                {{ $order->escrow->status }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('merchant.orders.show', $order) }}" class="text-indigo-500 hover:underline font-semibold">Manage</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                                        No orders received yet. Share your store link to start selling!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>

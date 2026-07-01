<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Order Details') }} — <span class="text-indigo-500 font-mono font-bold">{{ $order->order_number }}</span>
            </h2>
            <a href="{{ route('merchant.orders.index') }}" class="text-sm text-gray-500 hover:underline">← Back to Orders</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Details & Items -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Order Items -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-4">Items Ordered</h3>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($order->items as $item)
                                <div class="py-4 flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="w-16 h-16 rounded-xl object-cover border border-gray-100 dark:border-gray-700">
                                        <div>
                                            <h4 class="font-bold text-gray-900 dark:text-white">{{ $item->product->name }}</h4>
                                            <p class="text-xs text-gray-400">Quantity: {{ $item->quantity }} x ₦{{ number_format($item->price, 2) }}</p>
                                        </div>
                                    </div>
                                    <span class="font-bold text-gray-900 dark:text-white">₦{{ number_format($item->price * $item->quantity, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="border-t border-gray-100 dark:border-gray-700 pt-4 flex justify-between items-center font-black text-lg text-gray-900 dark:text-white">
                            <span>Total Amount</span>
                            <span>₦{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>

                    <!-- Customer & Shipping Info -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 space-y-6">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white">Customer & Shipping Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase">Customer Information</h4>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">{{ $order->customer->name }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $order->customer->phone }}</p>
                                <p class="text-xs text-gray-500">{{ $order->customer->email }}</p>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase">Shipping Address</h4>
                                <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-line">{{ $order->shipping_address }}</p>
                            </div>
                        </div>
                        @if($order->customer_notes)
                            <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                                <h4 class="text-xs font-bold text-gray-400 uppercase">Customer Notes</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 italic">"{{ $order->customer_notes }}"</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right Column: Escrow Controls & Audit Trail -->
                <div class="space-y-8">
                    <!-- Escrow Protection Status -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-4">Payment Protection</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <span class="text-xs text-gray-400 block">Payment Method</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $order->payment_method === 'escrow' ? 'Nomba Escrow (Protected)' : 'Standard Immediate Payment' }}
                                </span>
                            </div>
                            
                            <div>
                                <span class="text-xs text-gray-400 block">Nomba Reference</span>
                                <span class="text-xs font-mono text-gray-500 break-all">{{ $order->nomba_payment_reference ?? 'Pending Payment' }}</span>
                            </div>

                            @if($order->payment_method === 'escrow' && $order->escrow)
                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
                                    <span class="text-xs text-gray-400 block">Escrow Status</span>
                                    <span class="text-xl font-black uppercase tracking-wider mt-1 block
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
                                </div>

                                <!-- Action Forms -->
                                @if($order->escrow->status === 'held')
                                    <form action="{{ route('merchant.orders.shipping', $order) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="shipping_status" value="shipped">
                                        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl transition-all shadow-md active:scale-95 text-sm">
                                            Mark as Shipped / Dispatch Goods
                                        </button>
                                    </form>
                                @elseif($order->escrow->status === 'shipped')
                                    <form action="{{ route('merchant.orders.shipping', $order) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="shipping_status" value="delivered">
                                        <button type="submit" class="w-full py-3 bg-teal-500 hover:bg-teal-400 text-white font-semibold rounded-xl transition-all shadow-md active:scale-95 text-sm">
                                            Mark as Delivered
                                        </button>
                                    </form>
                                @elseif($order->escrow->status === 'delivered')
                                    <div class="p-3 bg-teal-500/10 border border-teal-500/20 text-teal-400 rounded-xl text-xs text-center">
                                        Awaiting buyer confirmation code. Buyer must confirm receipt to release funds.
                                    </div>
                                @elseif($order->escrow->status === 'disputed')
                                    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs space-y-3">
                                        <p class="font-bold">Dispute Raised by Buyer:</p>
                                        <p class="italic">"{{ $order->escrow->dispute_reason }}"</p>
                                        <p class="text-[10px] text-gray-500">You can contact the buyer to resolve. If you want to refund them, please contact Nomba support or trigger a refund in sandbox.</p>
                                    </div>
                                @elseif($order->escrow->status === 'released')
                                    <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl text-xs text-center font-semibold">
                                        ✓ Funds released & settled into your account.
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Escrow Timeline (Audit Trail) -->
                    @if($order->payment_method === 'escrow' && $order->escrow)
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                            <h3 class="font-bold text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Activity Log</h3>
                            <div class="space-y-4 relative before:absolute before:left-3.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-100 dark:before:bg-gray-700">
                                @foreach($order->escrow->events as $event)
                                    <div class="flex gap-4 relative">
                                        <div class="w-7.5 h-7.5 rounded-full bg-slate-100 dark:bg-gray-900 border-2 border-indigo-500 flex items-center justify-center z-10 text-xs">
                                            •
                                        </div>
                                        <div>
                                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block">{{ $event->description }}</span>
                                            <span class="text-[10px] text-gray-400 block mt-0.5">{{ $event->created_at->format('M d, H:i') }} • By {{ ucfirst($event->created_by) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

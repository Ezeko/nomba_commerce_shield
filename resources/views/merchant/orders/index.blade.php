<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Customer Orders') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm mb-6">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/40 text-xs font-bold text-gray-500 uppercase">
                                <th class="px-6 py-4">Order Number</th>
                                <th class="px-6 py-4">Customer</th>
                                <th class="px-6 py-4">Amount</th>
                                <th class="px-6 py-4">Protection</th>
                                <th class="px-6 py-4">Payment</th>
                                <th class="px-6 py-4">Escrow Status</th>
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                            @forelse($orders as $order)
                                <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-900/20">
                                    <td class="px-6 py-4 font-mono font-bold text-gray-900 dark:text-white">{{ $order->order_number }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $order->customer->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $order->customer->phone }}</div>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">₦{{ number_format($order->total_amount, 2) }}</td>
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
                                    <td class="px-6 py-4 text-gray-500">
                                        {{ $order->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('merchant.orders.show', $order) }}" class="text-indigo-500 hover:underline font-semibold">Manage</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-10 text-center text-gray-400">
                                        No orders received yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-6 border-t border-gray-100 dark:border-gray-700">
                    {{ $orders->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

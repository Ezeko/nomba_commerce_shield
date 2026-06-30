<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Manage Inventory') }}
            </h2>
            <a href="{{ route('merchant.products.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-md transition-all active:scale-95">
                + Add New Product
            </a>
        </div>
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
                                <th class="px-6 py-4">Image</th>
                                <th class="px-6 py-4">Product Name</th>
                                <th class="px-6 py-4">Price</th>
                                <th class="px-6 py-4">Stock</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                            @forelse($products as $product)
                                <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-900/20">
                                    <td class="px-6 py-4">
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-12 h-12 rounded-lg object-cover border border-gray-100 dark:border-gray-700">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $product->name }}</div>
                                        <div class="text-xs text-gray-400 truncate max-w-xs">{{ $product->description }}</div>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                                        ₦{{ number_format($product->price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                        {{ $product->stock }} in stock
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($product->is_active)
                                            <span class="px-2 py-1 rounded-md text-xs font-semibold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">Active</span>
                                        @else
                                            <span class="px-2 py-1 rounded-md text-xs font-semibold bg-gray-500/10 text-gray-400 border border-gray-500/20">Draft</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <a href="{{ route('merchant.products.edit', $product) }}" class="text-indigo-500 hover:underline font-semibold text-xs">Edit</a>
                                        <form action="{{ route('merchant.products.destroy', $product) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:underline font-semibold text-xs">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                                        You haven't added any products yet. Click "+ Add New Product" to start!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-6 border-t border-gray-100 dark:border-gray-700">
                    {{ $products->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

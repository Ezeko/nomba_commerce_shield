<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add New Product') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('merchant.products.store') }}" class="space-y-6">
                        @csrf

                        <!-- Product Name -->
                        <div>
                            <x-input-label for="name" :value="__('Product Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus placeholder="e.g. Vintage Leather Jacket" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="4" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full" placeholder="Describe your product (size, color, condition, etc.)..."></textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Price -->
                            <div>
                                <x-input-label for="price" :value="__('Price (NGN)')" />
                                <x-text-input id="price" class="block mt-1 w-full" type="number" step="0.01" name="price" :value="old('price')" required placeholder="e.g. 25000" />
                                <x-input-error :messages="$errors->get('price')" class="mt-2" />
                            </div>

                            <!-- Stock -->
                            <div>
                                <x-input-label for="stock" :value="__('Stock / Quantity')" />
                                <x-text-input id="stock" class="block mt-1 w-full" type="number" name="stock" :value="old('stock', 1)" required placeholder="e.g. 10" />
                                <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Image URL -->
                        <div>
                            <x-input-label for="image_url" :value="__('Product Image URL (Optional)')" />
                            <x-text-input id="image_url" class="block mt-1 w-full" type="url" name="image_url" :value="old('image_url')" placeholder="https://example.com/image.jpg" />
                            <p class="text-xs text-gray-500 mt-1">Leave blank to use a high-quality default product placeholder image.</p>
                            <x-input-error :messages="$errors->get('image_url')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-4 mt-6">
                            <a href="{{ route('merchant.products.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button class="bg-indigo-600 hover:bg-indigo-500 border-0 py-2.5 rounded-xl">
                                {{ __('Save Product') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

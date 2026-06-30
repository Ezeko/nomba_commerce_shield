<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Setup Your Social Store') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-2xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-teal-400">
                            Welcome to Nomba Commerce Shield
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            Create your store link to start accepting direct and escrow payments. We will automatically provision a dedicated Nomba Virtual Account for your store.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('merchant.store.store') }}" class="space-y-6">
                         @csrf

                         <!-- Store Name -->
                         <div>
                             <x-input-label for="name" :value="__('Store Name')" />
                             <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus placeholder="e.g. Ezekiel's Thrift Hub" />
                             <x-input-error :messages="$errors->get('name')" class="mt-2" />
                         </div>

                         <!-- Store Slug -->
                         <div>
                             <x-input-label for="slug" :value="__('Store URL Slug')" />
                             <div class="flex items-center mt-1">
                                 <span class="inline-flex items-center px-3 py-2 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-500 text-sm">
                                     {{ str_replace(['http://', 'https://'], '', url('/store')) }}/
                                 </span>
                                 <x-text-input id="slug" class="block w-full rounded-l-none" type="text" name="slug" :value="old('slug')" required placeholder="ezekiels-thrift" />
                             </div>
                             <p class="text-xs text-gray-500 mt-1">Only letters, numbers, and hyphens are allowed.</p>
                             <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                         </div>

                         <!-- Description -->
                         <div>
                             <x-input-label for="description" :value="__('Store Description')" />
                             <textarea id="description" name="description" rows="4" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full" placeholder="Tell your customers what you sell..."></textarea>
                             <x-input-error :messages="$errors->get('description')" class="mt-2" />
                         </div>

                         <!-- Notice -->
                         <div class="p-4 rounded-xl bg-teal-500/10 border border-teal-500/20 text-teal-400 text-xs flex items-start gap-3">
                             <span class="text-base">ℹ</span>
                             <div>
                                 <span class="font-bold">Nomba Integration:</span> By submitting this form, Nomba's API will generate a unique bank account number for your shop. Customers will be able to pay you via direct bank transfer and have the transaction auto-reconciled instantly.
                             </div>
                         </div>

                         <div class="flex items-center justify-end mt-4">
                             <x-primary-button class="ms-4 bg-gradient-to-r from-indigo-600 to-teal-500 hover:opacity-90 border-0 py-3 rounded-xl shadow-lg shadow-indigo-500/20">
                                 {{ __('Create My Store') }}
                             </x-primary-button>
                         </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Automatic slug generation
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        nameInput.addEventListener('keyup', function() {
            const name = this.value;
            slugInput.value = name
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '') // remove invalid chars
                .replace(/\s+/g, '-')        // replace spaces with hyphens
                .replace(/-+/g, '-');         // remove duplicate hyphens
        });
    </script>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Withdraw Funds') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Alerts -->
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
                <!-- Left: Withdrawal Form -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-6">Initiate Payout</h3>

                    <form method="POST" action="{{ route('merchant.withdraw.store') }}" class="space-y-6" id="withdraw-form">
                        @csrf
                        <input type="hidden" id="bank_name" name="bank_name">

                        <!-- Amount -->
                        <div>
                            <x-input-label for="amount" :value="__('Withdrawal Amount (NGN)')" />
                            <x-text-input id="amount" class="block mt-1 w-full" type="number" step="0.01" min="100" max="{{ $store->balance }}" name="amount" :value="old('amount')" required placeholder="e.g. 5000" />
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                            <p class="text-xs text-gray-500 mt-1">Minimum withdrawal is ₦100.00</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Select Bank -->
                            <div>
                                <x-input-label for="bank_code" :value="__('Select Bank')" />
                                <select id="bank_code" name="bank_code" required class="w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="">-- Select Bank --</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank['code'] }}">{{ $bank['name'] }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('bank_code')" class="mt-2" />
                            </div>

                            <!-- Account Number -->
                            <div>
                                <x-input-label for="account_number" :value="__('Account Number')" />
                                <x-text-input id="account_number" class="block mt-1 w-full" type="text" maxlength="10" name="account_number" :value="old('account_number')" required placeholder="10-digit NUBAN" />
                                <x-input-error :messages="$errors->get('account_number')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Account Name -->
                        <div>
                            <x-input-label for="account_name" :value="__('Account Name')" />
                            <x-text-input id="account_name" class="block mt-1 w-full" type="text" name="account_name" :value="old('account_name')" required placeholder="e.g. Ezekiel John" />
                            <x-input-error :messages="$errors->get('account_name')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button class="bg-indigo-600 hover:bg-indigo-500 border-0 py-3 rounded-xl shadow-lg shadow-indigo-500/20">
                                {{ __('Request Withdrawal') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>

                <!-- Right: Balance & Overview -->
                <div class="space-y-8">
                    <!-- Balance Card -->
                    <div class="bg-gradient-to-br from-teal-900 to-emerald-950 p-6 rounded-2xl shadow-lg border border-teal-500/20 text-white">
                        <span class="text-xs font-bold text-teal-300 uppercase tracking-widest block">Available Balance</span>
                        <div class="text-4xl font-black mt-2">₦{{ number_format($store->balance, 2) }}</div>
                        <p class="text-xs text-teal-200/60 mt-4 leading-relaxed">
                            This represents cleared funds from completed escrow deliveries and standard payments.
                        </p>
                    </div>

                    <!-- Notice Card -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 text-sm space-y-3">
                        <h4 class="font-bold text-gray-950 dark:text-white">Withdrawal Notice</h4>
                        <ul class="space-y-2 text-gray-600 dark:text-gray-400 text-xs list-disc pl-4 leading-relaxed">
                            <li>Withdrawals are processed instantly via **Nomba Transfers**.</li>
                            <li>Ensure that your bank account details are correct. Failed transfers will be refunded back to your wallet.</li>
                            <li>Standard transfer charges may apply.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Withdrawal History Table -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">Withdrawal History</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/40 text-xs font-bold text-gray-500 uppercase">
                                <th class="px-6 py-4">Reference</th>
                                <th class="px-6 py-4">Bank</th>
                                <th class="px-6 py-4">Account Number</th>
                                <th class="px-6 py-4">Account Name</th>
                                <th class="px-6 py-4">Amount</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                            @forelse($withdrawals as $wth)
                                <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-900/20">
                                    <td class="px-6 py-4 font-mono font-bold text-gray-900 dark:text-white">{{ $wth->reference }}</td>
                                    <td class="px-6 py-4">{{ $wth->bank_name }}</td>
                                    <td class="px-6 py-4 font-mono">{{ $wth->account_number }}</td>
                                    <td class="px-6 py-4">{{ $wth->account_name }}</td>
                                    <td class="px-6 py-4 font-bold">₦{{ number_format($wth->amount, 2) }}</td>
                                    <td class="px-6 py-4">
                                        @if($wth->status === 'success')
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">Success</span>
                                        @elseif($wth->status === 'failed')
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-rose-500/10 text-rose-500 border border-rose-500/20">Failed</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-amber-500/10 text-amber-500 border border-amber-500/20">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-550">{{ $wth->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-455 text-gray-400">
                                        No withdrawals requested yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-6 border-t border-gray-100 dark:border-gray-700">
                    {{ $withdrawals->links() }}
                </div>
            </div>

        </div>
    </div>

    <!-- Script to copy bank name -->
    <script>
        const bankSelect = document.getElementById('bank_code');
        const bankNameInput = document.getElementById('bank_name');

        bankSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            bankNameInput.value = selectedOption.text;
        });
    </script>
</x-app-layout>

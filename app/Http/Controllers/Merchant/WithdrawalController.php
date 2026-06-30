<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWithdrawalRequest;
use App\Models\Withdrawal;
use App\Services\NombaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WithdrawalController extends Controller
{
    protected NombaService $nombaService;

    public function __construct(NombaService $nombaService)
    {
        $this->nombaService = $nombaService;
    }

    /**
     * Show the withdrawal form and history
     */
    public function create()
    {
        $store = Auth::user()->store;
        if (!$store) {
            return redirect()->route('merchant.store.create');
        }

        $withdrawals = $store->withdrawals()->latest()->paginate(10);

        // Top Nigerian banks
        $banks = [
            ['code' => '044', 'name' => 'Access Bank'],
            ['code' => '050', 'name' => 'Ecobank'],
            ['code' => '070', 'name' => 'Fidelity Bank'],
            ['code' => '011', 'name' => 'First Bank of Nigeria'],
            ['code' => '058', 'name' => 'Guaranty Trust Bank (GTBank)'],
            ['code' => '999992', 'name' => 'OPay'],
            ['code' => '999991', 'name' => 'PalmPay'],
            ['code' => '076', 'name' => 'Polaris Bank'],
            ['code' => '221', 'name' => 'Stanbic IBTC Bank'],
            ['code' => '232', 'name' => 'Sterling Bank'],
            ['code' => '032', 'name' => 'Union Bank of Nigeria'],
            ['code' => '033', 'name' => 'United Bank for Africa (UBA)'],
            ['code' => '035', 'name' => 'Wema Bank'],
            ['code' => '057', 'name' => 'Zenith Bank'],
        ];

        return view('merchant.withdraw.create', compact('store', 'withdrawals', 'banks'));
    }

    /**
     * Process the withdrawal request using Nomba transfer API
     */
    public function store(StoreWithdrawalRequest $request)
    {
        $store = Auth::user()->store;
        $amount = (float) $request->amount;
        $reference = 'wth_' . Str::random(12);

        return DB::transaction(function () use ($request, $store, $amount, $reference) {
            // 1. Deduct the wallet balance immediately to lock the funds
            $store->decrement('balance', $amount);

            // 2. Create the withdrawal record
            $withdrawal = Withdrawal::create([
                'store_id' => $store->id,
                'amount' => $amount,
                'bank_code' => $request->bank_code,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'status' => 'pending',
                'reference' => $reference,
            ]);

            // 3. Initiate the payout via Nomba API
            $payoutResult = $this->nombaService->transferFunds(
                $amount,
                $request->account_number,
                $request->bank_code,
                $request->account_name,
                $reference
            );

            if ($payoutResult['success']) {
                $withdrawal->update(['status' => 'success']);
                return redirect()->route('merchant.withdraw.create')
                    ->with('success', 'Withdrawal of ₦' . number_format($amount, 2) . ' was processed successfully!');
            }

            // 4. If payout fails, roll back the deduction (refund the wallet)
            $store->increment('balance', $amount);
            $withdrawal->update(['status' => 'failed']);

            Log::error("Withdrawal Payout Failed for Ref {$reference}: " . ($payoutResult['message'] ?? 'Unknown error'));
            return redirect()->route('merchant.withdraw.create')
                ->with('error', 'Nomba Payout failed: ' . ($payoutResult['message'] ?? 'Please verify your account details.'));
        });
    }
}

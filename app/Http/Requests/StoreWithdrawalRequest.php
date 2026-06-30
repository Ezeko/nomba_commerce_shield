<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreWithdrawalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $store = Auth::user()->store;
        $maxBalance = $store ? $store->balance : 0;

        return [
            'amount' => ['required', 'numeric', 'min:100', 'max:' . $maxBalance],
            'bank_code' => ['required', 'string', 'max:10'],
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'size:10'],
            'account_name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'amount.max' => 'You do not have enough funds in your wallet to withdraw this amount.',
            'account_number.size' => 'The account number must be exactly 10 digits.',
        ];
    }
}

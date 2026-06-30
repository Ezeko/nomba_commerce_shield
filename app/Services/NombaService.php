<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NombaService
{
    protected string $baseUrl;
    protected ?string $clientId;
    protected ?string $clientSecret;
    protected ?string $accountId;
    protected ?string $subAccountId;

    public function __construct()
    {
        $this->clientId = config('services.nomba.client_id') ?: '706df6c4-b8bb-4130-88c4-d21b052f8631';
        $this->clientSecret = config('services.nomba.client_secret') ?: 'k8UobYk3APgOoxUnNL7VpuxwTsH4LsXtydfjcHs8RH0YISBB4OMqJsaafG+U8fWETu9YZ96bNXE+DelCDuMPw==';
        $this->accountId = config('services.nomba.account_id') ?: 'f666ef9b-888e-4799-85ce-acb505b28023';
        $this->subAccountId = config('services.nomba.sub_account_id') ?: 'cb124f2f-bf21-47ca-b69d-1e676df0cc41';
        
        // If we are using the fallback credentials, we force the sandbox URL
        if (empty(config('services.nomba.client_id'))) {
            $this->baseUrl = 'https://sandbox.nomba.com';
            Log::info('NombaService: Using fallback Sandbox TEST credentials.');
        } else {
            $this->baseUrl = config('services.nomba.base_url', 'https://api.nomba.com');
        }
    }

    /**
     * Get OAuth2 Access Token
     */
    protected function getAccessToken(): ?string
    {
        $cachedToken = Cache::get('nomba_access_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        try {
            $response = Http::withHeaders([
                'accountId' => $this->accountId,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/v1/auth/token/issue", [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if ($response->successful()) {
                $token = $response->json('access_token');
                if ($token) {
                    Cache::put('nomba_access_token', $token, 3500);
                    return $token;
                }
            }

            Log::error('Nomba Auth Failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Nomba Auth Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get headers for Nomba API requests
     */
    protected function getHeaders(): array
    {
        $token = $this->getAccessToken();
        
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
            $headers['accountId'] = $this->accountId;
        } else {
            Log::warning('NombaService: Token is null. Sending request in unauthenticated sandbox mode.');
        }

        return $headers;
    }

    /**
     * Create a Checkout Order Link
     */
    public function createCheckout(array $data): array
    {
        $orderRef = $data['orderReference'] ?? 'order_' . Str::random(10);
        $amount = $data['amount'];
        $email = $data['customerEmail'] ?? 'customer@example.com';
        $callbackUrl = $data['callbackUrl'] ?? route('webhooks.nomba');

        // Always make a real API call to create the checkout link


        try {
            $response = Http::withHeaders($this->getHeaders())
                ->post("{$this->baseUrl}/v1/checkout/order", [
                    'order' => [
                        'amount' => (float) $amount,
                        'currency' => 'NGN',
                        'orderReference' => $orderRef,
                        'customerEmail' => $email,
                        'customerId' => $data['customerId'] ?? 'cust_' . Str::random(8),
                        'callbackUrl' => $callbackUrl,
                    ],
                    'tokenizeCard' => false
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => [
                        'orderReference' => $response->json('data.orderReference'),
                        'checkoutUrl' => $response->json('data.checkoutLink') ?? $response->json('data.checkoutUrl') ?? $response->json('data.paymentUrl'),
                        'amount' => $amount
                    ]
                ];
            }

            Log::error('Nomba Checkout Creation Failed: ' . $response->body());
            return ['success' => false, 'message' => $response->json('description', 'Unknown API error')];
        } catch (\Exception $e) {
            Log::error('Nomba Checkout Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Create a Virtual Account for a merchant store
     */
    public function createVirtualAccount(string $storeName, string $storeSlug): array
    {
        $accountRef = 'va_' . Str::slug($storeSlug) . '_' . Str::random(4);

        // Always make a real API call to create the virtual account


        try {
            $response = Http::withHeaders($this->getHeaders())
                ->post("{$this->baseUrl}/v1/accounts/virtual", [
                    'accountRef' => $accountRef,
                    'accountName' => 'NCS - ' . $storeName,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => [
                        'accountNumber' => $response->json('data.bankAccountNumber') ?? $response->json('data.accountNumber'),
                        'bankName' => $response->json('data.bankName', 'Nomba MFB'),
                        'accountName' => $response->json('data.bankAccountName') ?? $response->json('data.accountName'),
                        'accountRef' => $response->json('data.accountRef'),
                    ]
                ];
            }

            Log::error('Nomba Virtual Account Creation Failed: ' . $response->body());
            return ['success' => false, 'message' => $response->json('description', 'Failed to create virtual account')];
        } catch (\Exception $e) {
            Log::error('Nomba Virtual Account Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Transfer/Payout funds (e.g. release from escrow to merchant bank account)
     */
    public function transferFunds(float $amount, string $accountNumber, string $bankCode, string $accountName, string $reference): array
    {
        // Always make a real API call to transfer the funds


        try {
            $endpoint = $this->subAccountId 
                ? "{$this->baseUrl}/v2/transfers/bank/{$this->subAccountId}" 
                : "{$this->baseUrl}/v2/transfers/bank";

            $response = Http::withHeaders($this->getHeaders())
                ->post($endpoint, [
                    'amount' => $amount,
                    'accountNumber' => $accountNumber,
                    'accountName' => $accountName,
                    'bankCode' => $bankCode,
                    'merchantTxRef' => $reference,
                    'senderName' => 'Nomba Commerce Shield',
                    'narration' => 'Escrow Release Payout - Ref: ' . $reference,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => [
                        'transactionReference' => $response->json('data.transactionReference'),
                        'status' => $response->json('data.status'),
                    ]
                ];
            }

            Log::error('Nomba Payout Transfer Failed: ' . $response->body());
            return ['success' => false, 'message' => $response->json('description', 'Transfer failed')];
        } catch (\Exception $e) {
            Log::error('Nomba Payout Transfer Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

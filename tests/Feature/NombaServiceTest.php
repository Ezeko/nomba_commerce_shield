<?php

namespace Tests\Feature;

use App\Services\NombaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NombaServiceTest extends TestCase
{
    protected NombaService $nombaService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear cache before each test
        Cache::forget('nomba_access_token');

        // Configure test env variables
        config([
            'services.nomba.base_url' => 'https://sandbox.nomba.com',
            'services.nomba.client_id' => 'test-client-id',
            'services.nomba.client_secret' => 'test-client-secret',
            'services.nomba.account_id' => 'test-account-id',
            'services.nomba.sub_account_id' => 'test-sub-account-id',
        ]);

        $this->nombaService = new NombaService();
    }

    /**
     * Test successful access token retrieval and caching.
     */
    public function test_it_retrieves_and_caches_access_token_successfully(): void
    {
        Http::fake([
            'https://sandbox.nomba.com/v1/auth/token/issue' => Http::response([
                'access_token' => 'mock-jwt-token',
                'expires_in' => 3600
            ], 200)
        ]);

        // First call should hit the API
        $token = $this->getPrivateMethodResult('getAccessToken');
        $this->assertEquals('mock-jwt-token', $token);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://sandbox.nomba.com/v1/auth/token/issue'
                && $request['grant_type'] === 'client_credentials'
                && $request['client_id'] === 'test-client-id'
                && $request->hasHeader('accountId', 'test-account-id');
        });

        // Second call should fetch from cache without hitting the API again
        Http::fake([
            'https://sandbox.nomba.com/v1/auth/token/issue' => Http::response([], 500)
        ]);

        $cachedToken = $this->getPrivateMethodResult('getAccessToken');
        $this->assertEquals('mock-jwt-token', $cachedToken);
    }

    /**
     * Test successful checkout link creation.
     */
    public function test_it_creates_checkout_link_successfully(): void
    {
        Http::fake([
            'https://sandbox.nomba.com/v1/auth/token/issue' => Http::response([
                'access_token' => 'mock-jwt-token'
            ], 200),
            'https://sandbox.nomba.com/v1/checkout/order' => Http::response([
                'data' => [
                    'orderReference' => 'order_ref_123',
                    'checkoutUrl' => 'https://checkout.nomba.com/pay/order_ref_123'
                ]
            ], 200)
        ]);

        $result = $this->nombaService->createCheckout([
            'orderReference' => 'order_ref_123',
            'amount' => 1500.00,
            'customerEmail' => 'buyer@example.com',
            'customerId' => 'cust_999'
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('order_ref_123', $result['data']['orderReference']);
        $this->assertEquals('https://checkout.nomba.com/pay/order_ref_123', $result['data']['checkoutUrl']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://sandbox.nomba.com/v1/checkout/order'
                && $request['order']['amount'] === 1500.00
                && $request['order']['orderReference'] === 'order_ref_123'
                && $request->hasHeader('Authorization', 'Bearer mock-jwt-token');
        });
    }

    /**
     * Test successful virtual account creation.
     */
    public function test_it_creates_virtual_account_successfully(): void
    {
        Http::fake([
            'https://sandbox.nomba.com/v1/auth/token/issue' => Http::response([
                'access_token' => 'mock-jwt-token'
            ], 200),
            'https://sandbox.nomba.com/v1/accounts/virtual' => Http::response([
                'data' => [
                    'accountNumber' => '1234567890',
                    'bankName' => 'Nomba Microfinance Bank',
                    'accountName' => 'NCS - Test Store',
                    'accountRef' => 'va_ref_456'
                ]
            ], 200)
        ]);

        $result = $this->nombaService->createVirtualAccount('Test Store', 'test-store');

        $this->assertTrue($result['success']);
        $this->assertEquals('1234567890', $result['data']['accountNumber']);
        $this->assertEquals('Nomba Microfinance Bank', $result['data']['bankName']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://sandbox.nomba.com/v1/accounts/virtual'
                && $request['accountName'] === 'NCS - Test Store'
                && $request->hasHeader('accountId', 'test-sub-account-id');
        });
    }

    /**
     * Test successful bank transfer payout routed to sub-account.
     */
    public function test_it_routes_transfers_to_sub_account_successfully(): void
    {
        Http::fake([
            'https://sandbox.nomba.com/v1/auth/token/issue' => Http::response([
                'access_token' => 'mock-jwt-token'
            ], 200),
            'https://sandbox.nomba.com/v2/transfers/bank/test-sub-account-id' => Http::response([
                'data' => [
                    'transactionReference' => 'payout_ref_789',
                    'status' => 'SUCCESS'
                ]
            ], 200)
        ]);

        $result = $this->nombaService->transferFunds(
            5000.00,
            '0012345678',
            '058',
            'John Doe',
            'wth_789'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('payout_ref_789', $result['data']['transactionReference']);
        $this->assertEquals('SUCCESS', $result['data']['status']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://sandbox.nomba.com/v2/transfers/bank/test-sub-account-id'
                && $request['amount'] === 5000.00
                && $request['accountNumber'] === '0012345678'
                && $request['bankCode'] === '058';
        });
    }

    /**
     * Helper to invoke protected/private methods on NombaService.
     */
    protected function getPrivateMethodResult(string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(NombaService::class);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($this->nombaService, $parameters);
    }
}

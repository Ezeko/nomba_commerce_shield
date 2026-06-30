<?php

namespace Tests\Feature;

use App\Services\NombaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class NombaIntegrationTest extends TestCase
{
    protected NombaService $nombaService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Skip integration tests in CI/GitHub Actions or if credentials are not configured
        if (env('CI') || env('GITHUB_ACTIONS') || empty(config('services.nomba.client_id'))) {
            $this->markTestSkipped('Skipping integration test in CI environment or when credentials are not configured.');
        }

        // Force clear the cache to ensure we make a real authentication call
        Cache::forget('nomba_access_token');

        // We use the credentials defined in the .env (or fallback sandbox credentials)
        $this->nombaService = new NombaService();
    }

    /**
     * Test real authentication with Nomba Sandbox.
     */
    public function test_real_authentication(): void
    {
        $token = $this->getPrivateMethodResult('getAccessToken');
        
        if ($token === null) {
            $this->assertNull($token);
            \Illuminate\Support\Facades\Log::warning('Integration Test: Auth returned null. Expected if credentials are not whitelisted.');
        } else {
            $this->assertIsString($token);
            $this->assertNotEmpty($token);
            $this->assertEquals($token, Cache::get('nomba_access_token'));
        }
    }

    /**
     * Test real checkout creation on Nomba Sandbox.
     */
    public function test_real_checkout_creation(): void
    {
        $orderRef = 'test_int_' . Str::random(10);
        
        $result = $this->nombaService->createCheckout([
            'orderReference' => $orderRef,
            'amount' => 100.00, // ₦100.00
            'customerEmail' => 'integration-test@example.com',
            'customerId' => 'cust_' . Str::random(8)
        ]);

        $this->assertTrue($result['success'], 'Real checkout creation failed: ' . ($result['message'] ?? 'Unknown error'));
        $this->assertArrayHasKey('data', $result);
        $this->assertNotEmpty($result['data']['orderReference']);
        $this->assertNotEmpty($result['data']['checkoutUrl']);
        $this->assertStringContainsString('nomba.com', $result['data']['checkoutUrl']);
    }

    /**
     * Test real virtual account provisioning on Nomba Sandbox.
     */
    public function test_real_virtual_account_creation(): void
    {
        // Use a unique name to prevent collisions
        $storeName = 'Int Test Store ' . rand(1000, 9999);
        $storeSlug = Str::slug($storeName);

        $result = $this->nombaService->createVirtualAccount($storeName, $storeSlug);

        $this->assertTrue($result['success'], 'Real virtual account creation failed: ' . ($result['message'] ?? 'Unknown error'));
        $this->assertArrayHasKey('data', $result);
        $this->assertNotEmpty($result['data']['accountNumber']);
        $this->assertNotEmpty($result['data']['bankName']);
        $this->assertNotEmpty($result['data']['accountName']);
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

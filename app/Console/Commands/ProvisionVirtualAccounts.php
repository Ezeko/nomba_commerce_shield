<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Services\NombaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProvisionVirtualAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stores:provision-virtual-accounts';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Automatically provision Nomba virtual accounts for stores that do not have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('stores:provision-virtual-accounts: Starting background provisioning...');
        $this->info('Starting virtual account provisioning for stores...');

        // Find stores that do not have a virtual account number
        $stores = Store::whereNull('virtual_account_number')->get();

        if ($stores->isEmpty()) {
            $this->info('No stores require virtual account provisioning.');
            Log::info('stores:provision-virtual-accounts: No stores require provisioning.');
            return 0;
        }

        $nombaService = app(NombaService::class);
        $successCount = 0;
        $failCount = 0;

        foreach ($stores as $store) {
            $this->info("Provisioning virtual account for store: {$store->name} (ID: {$store->id})...");

            try {
                $result = $nombaService->createVirtualAccount($store->name, $store->slug);

                if ($result['success']) {
                    $store->update([
                        'virtual_account_number' => $result['data']['accountNumber'],
                        'virtual_account_bank' => $result['data']['bankName'],
                        'virtual_account_name' => $result['data']['accountName'],
                    ]);

                    $this->info("Successfully provisioned virtual account for store: {$store->name}");
                    Log::info("stores:provision-virtual-accounts: Successfully provisioned for store: {$store->name}");
                    $successCount++;
                } else {
                    $message = $result['message'] ?? 'Unknown error';
                    $this->error("Failed to provision for store {$store->name}: {$message}");
                    Log::error("stores:provision-virtual-accounts: Failed to provision for store {$store->name}: {$message}");
                    $failCount++;
                }
            } catch (\Exception $e) {
                $this->error("Exception provisioning for store {$store->name}: " . $e->getMessage());
                Log::error("stores:provision-virtual-accounts: Exception for store {$store->name}: " . $e->getMessage(), [
                    'exception' => $e
                ]);
                $failCount++;
            }
        }

        $this->info("Finished. Success: {$successCount}, Failed: {$failCount}");
        Log::info("stores:provision-virtual-accounts: Finished. Success: {$successCount}, Failed: {$failCount}");
        
        return 0;
    }
}

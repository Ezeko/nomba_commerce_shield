<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Escrow;
use App\Models\EscrowEvent;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EscrowEngine
{
    protected NombaService $nombaService;

    public function __construct(NombaService $nombaService)
    {
        $this->nombaService = $nombaService;
    }

    /**
     * Initialize an Escrow for an Order
     */
    public function holdPayment(Order $order): Escrow
    {
        return DB::transaction(function () use ($order) {
            // Generate a secure 6-digit release code for the buyer
            $releaseCode = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

            $escrow = Escrow::create([
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'status' => 'held', // Funds are now locked
                'release_code' => $releaseCode,
            ]);

            $this->logEvent($escrow, 'pending', 'held', 'Payment received and locked in escrow. Release code generated.', 'system');

            $order->update([
                'payment_status' => 'paid',
                'shipping_status' => 'pending',
            ]);

            return $escrow;
        });
    }

    /**
     * Merchant marks the order as shipped
     */
    public function markAsShipped(Escrow $escrow): bool
    {
        if ($escrow->status !== 'held') {
            return false;
        }

        return DB::transaction(function () use ($escrow) {
            $escrow->update(['status' => 'shipped']);
            $this->logEvent($escrow, 'held', 'shipped', 'Merchant has shipped the package.', 'seller');
            
            $escrow->order->update(['shipping_status' => 'shipped']);
            return true;
        });
    }

    /**
     * Mark the order as delivered
     */
    public function markAsDelivered(Escrow $escrow): bool
    {
        if ($escrow->status !== 'shipped') {
            return false;
        }

        return DB::transaction(function () use ($escrow) {
            $escrow->update(['status' => 'delivered']);
            $this->logEvent($escrow, 'shipped', 'delivered', 'Package delivered. Awaiting buyer confirmation code.', 'system');
            
            $escrow->order->update(['shipping_status' => 'delivered']);
            return true;
        });
    }

    /**
     * Release Escrow funds to the Merchant
     */
    public function releaseFunds(Escrow $escrow, string $enteredCode): array
    {
        // Trim code to avoid white space errors
        $enteredCode = trim($enteredCode);

        if ($escrow->status === 'released') {
            return ['success' => false, 'message' => 'Funds have already been released.'];
        }

        if (!in_array($escrow->status, ['held', 'shipped', 'delivered', 'disputed'])) {
            return ['success' => false, 'message' => 'Invalid escrow state for release.'];
        }

        if ($escrow->release_code !== $enteredCode) {
            return ['success' => false, 'message' => 'Incorrect confirmation release code.'];
        }

        return DB::transaction(function () use ($escrow) {
            $store = $escrow->order->store;
            $user = $store->user;

            // we credit the merchant's in-app store wallet balance directly.
            $store->increment('balance', $escrow->amount);

            $oldStatus = $escrow->status;
            $escrow->update([
                'status' => 'released',
                'released_at' => now(),
            ]);

            $this->logEvent($escrow, $oldStatus, 'released', 'Buyer confirmed receipt. Funds released to merchant.', 'buyer');

            $escrow->order->update([
                'shipping_status' => 'received',
                'payment_status' => 'paid'
            ]);

            // Recalculate Trust Score
            $this->recalculateTrustScore($store);

            return ['success' => true, 'message' => 'Funds successfully released to your account!'];
        });
    }

    /**
     * Buyer raises a dispute
     */
    public function disputeEscrow(Escrow $escrow, string $reason): bool
    {
        if (!in_array($escrow->status, ['held', 'shipped', 'delivered'])) {
            return false;
        }

        return DB::transaction(function () use ($escrow, $reason) {
            $oldStatus = $escrow->status;
            $escrow->update([
                'status' => 'disputed',
                'dispute_reason' => $reason,
                'disputed_at' => now(),
            ]);

            $this->logEvent($escrow, $oldStatus, 'disputed', 'Buyer raised a dispute. Reason: ' . $reason, 'buyer');

            // Recalculate Trust Score (disputes lower the score immediately)
            $this->recalculateTrustScore($escrow->order->store);

            return true;
        });
    }

    /**
     * Refund Escrow funds to the Buyer
     */
    public function refundEscrow(Escrow $escrow): array
    {
        if ($escrow->status !== 'disputed') {
            return ['success' => false, 'message' => 'Only disputed escrows can be refunded.'];
        }

        return DB::transaction(function () use ($escrow) {
            // In a real scenario, we would trigger a refund transaction via Nomba
            // For the hackathon, we simulate the refund transfer back to the buyer
            $oldStatus = $escrow->status;
            $escrow->update([
                'status' => 'refunded',
                'refunded_at' => now(),
            ]);

            $this->logEvent($escrow, $oldStatus, 'refunded', 'Dispute resolved. Funds refunded to buyer.', 'admin');

            $escrow->order->update([
                'payment_status' => 'refunded',
            ]);

            // Recalculate Trust Score
            $this->recalculateTrustScore($escrow->order->store);

            return ['success' => true, 'message' => 'Escrow refunded successfully.'];
        });
    }

    /**
     * Recalculate Merchant Trust Score (0-100)
     */
    public function recalculateTrustScore(Store $store): void
    {
        $totalEscrows = Escrow::whereHas('order', function ($query) use ($store) {
            $query->where('store_id', $store->id);
        })->count();

        if ($totalEscrows === 0) {
            $store->update(['trust_score' => 100]);
            return;
        }

        $releasedEscrows = Escrow::whereHas('order', function ($query) use ($store) {
            $query->where('store_id', $store->id);
        })->where('status', 'released')->count();

        $disputedEscrows = Escrow::whereHas('order', function ($query) use ($store) {
            $query->where('store_id', $store->id);
        })->whereIn('status', ['disputed', 'refunded'])->count();

        // Formula:
        // Start at 90 (neutral baseline for new stores with at least 1 txn)
        // Add 2 points for every successful release
        // Subtract 15 points for every dispute/refund
        // Cap between 10 and 100
        $score = 90 + ($releasedEscrows * 2) - ($disputedEscrows * 15);
        $score = max(10, min(100, $score));

        $store->update(['trust_score' => $score]);
    }

    /**
     * Log an escrow status transition event
     */
    protected function logEvent(Escrow $escrow, string $from, string $to, string $description, string $createdBy): void
    {
        EscrowEvent::create([
            'escrow_id' => $escrow->id,
            'from_status' => $from,
            'to_status' => $to,
            'description' => $description,
            'created_by' => $createdBy,
        ]);
    }
}

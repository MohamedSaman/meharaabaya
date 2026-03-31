<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_number',
        'purchase_order_id',
        'supplier_price',
        'selling_price',
        'quantity',
        'remaining_quantity',
        'received_date',
        'status',
    ];

    protected $casts = [
        'received_date' => 'date',
        'supplier_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    /**
     * Get the product that owns this batch
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductDetail::class, 'product_id');
    }

    /**
     * Get the purchase order that created this batch
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    /**
     * Generate a unique batch number
     */
    public static function generateBatchNumber($productId)
    {
        $prefix = 'BATCH-' . $productId . '-';
        $date = now()->format('Ymd');
        $count = self::where('product_id', $productId)
            ->whereDate('created_at', now())
            ->count() + 1;

        return $prefix . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get active batches for a product using FIFO (oldest first)
     */
    public static function getActiveBatches($productId)
    {
        return self::where('product_id', $productId)
            ->where('status', 'active')
            ->where('remaining_quantity', '>', 0)
            ->orderBy('received_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Check if batch is depleted
     */
    public function isDepleted()
    {
        return $this->remaining_quantity == 0 || $this->status == 'depleted';
    }

    /**
     * Get or create default batch for a product
     * Default batch is always active and used for stock adjustments
     */
    public static function getOrCreateDefaultBatch($productId)
    {
        // Look for existing default batch
        $defaultBatch = self::where('product_id', $productId)
            ->where('batch_number', 'like', 'DEFAULT-%')
            ->first();

        if (!$defaultBatch) {
            // Get product prices
            $productPrice = \App\Models\ProductPrice::where('product_id', $productId)->first();

            // Create default batch
            $defaultBatch = self::create([
                'product_id' => $productId,
                'batch_number' => 'DEFAULT-' . $productId,
                'purchase_order_id' => null,
                'supplier_price' => $productPrice->supplier_price ?? 0,
                'selling_price' => $productPrice->selling_price ?? 0,
                'quantity' => 0,
                'remaining_quantity' => 0,
                'received_date' => now(),
                'status' => 'active',
            ]);

            \Illuminate\Support\Facades\Log::info("Created default batch for product #{$productId}");
        }

        return $defaultBatch;
    }

    /**
     * Check if this is a default batch
     */
    public function isDefaultBatch()
    {
        return strpos($this->batch_number, 'DEFAULT-') === 0;
    }

    /**
     * Override deduct method to prevent default batch from becoming depleted
     */
    public function deduct($quantity)
    {
        if ($quantity > $this->remaining_quantity) {
            throw new \Exception("Insufficient quantity in batch {$this->batch_number}");
        }

        $this->remaining_quantity -= $quantity;

        // Default batches should never be marked as depleted
        if ($this->remaining_quantity == 0 && !$this->isDefaultBatch()) {
            $this->status = 'depleted';
        }

        $this->save();

        return $this;
    }
}

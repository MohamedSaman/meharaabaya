<?php

namespace App\Services;

use App\Models\ProductBatch;
use App\Models\ProductStock;
use App\Models\ProductPrice;
use App\Models\ProductDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FIFOStockService
{
    /**
     * Deduct stock from batches using FIFO method
     * Returns array with deduction details
     */
    public static function deductStock($productId, $quantity)
    {
        $remainingQty = $quantity;
        $deductions = [];
        $totalCost = 0;

        // Get active batches in FIFO order (oldest first)
        $batches = ProductBatch::getActiveBatches($productId);

        if ($batches->isEmpty()) {
            throw new \Exception("No active batches found for product ID: {$productId}");
        }

        // Check if we have enough total stock
        $totalAvailable = $batches->sum('remaining_quantity');
        if ($totalAvailable < $quantity) {
            throw new \Exception("Insufficient stock. Required: {$quantity}, Available: {$totalAvailable}");
        }

        DB::beginTransaction();
        try {
            $batchDepleted = false;

            foreach ($batches as $batch) {
                if ($remainingQty <= 0) break;

                $deductQty = min($remainingQty, $batch->remaining_quantity);

                // Check if this batch will be depleted
                $willBeDepleted = ($deductQty == $batch->remaining_quantity);

                // Deduct from batch
                $batch->deduct($deductQty);

                if ($willBeDepleted) {
                    $batchDepleted = true;
                }

                // Track deduction details
                $deductions[] = [
                    'batch_id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'quantity' => $deductQty,
                    'supplier_price' => $batch->supplier_price,
                    'selling_price' => $batch->selling_price,
                    'cost' => $batch->supplier_price * $deductQty,
                ];

                $totalCost += $batch->supplier_price * $deductQty;
                $remainingQty -= $deductQty;
            }

            // Update product stock totals
            $stock = ProductStock::where('product_id', $productId)->first();
            if ($stock) {
                $stock->available_stock -= $quantity;
                $stock->sold_count += $quantity;
                $stock->save();
            }

            // If any batch was depleted, check and update main prices
            // This ensures prices reflect the current oldest active batch
            if ($batchDepleted) {
                self::updateMainPrices($productId);
            }

            DB::commit();
            return [
                'success' => true,
                'deductions' => $deductions,
                'total_cost' => $totalCost,
                'average_cost' => $quantity > 0 ? $totalCost / $quantity : 0,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update main product prices when a batch is depleted
     * Uses the oldest active batch prices (next in FIFO queue)
     */
    public static function updateMainPrices($productId)
    {
        // Get the next active batch (if any)
        $nextBatch = ProductBatch::where('product_id', $productId)
            ->where('status', 'active')
            ->where('remaining_quantity', '>', 0)
            ->orderBy('received_date', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        if ($nextBatch) {
            // Update main product prices to reflect the next batch
            $productPrice = ProductPrice::where('product_id', $productId)->first();

            if ($productPrice) {
                $oldSupplierPrice = $productPrice->supplier_price;
                $oldSellingPrice = $productPrice->selling_price;

                $productPrice->supplier_price = $nextBatch->supplier_price;
                $productPrice->selling_price = $nextBatch->selling_price;
                $productPrice->save();

                Log::info("Product #{$productId} prices updated", [
                    'old_supplier_price' => $oldSupplierPrice,
                    'new_supplier_price' => $nextBatch->supplier_price,
                    'old_selling_price' => $oldSellingPrice,
                    'new_selling_price' => $nextBatch->selling_price,
                    'batch_number' => $nextBatch->batch_number,
                ]);
            } else {
                // Create price record if doesn't exist
                ProductPrice::create([
                    'product_id' => $productId,
                    'supplier_price' => $nextBatch->supplier_price,
                    'selling_price' => $nextBatch->selling_price,
                    'discount_price' => 0,
                ]);

                Log::info("Product #{$productId} price record created", [
                    'supplier_price' => $nextBatch->supplier_price,
                    'selling_price' => $nextBatch->selling_price,
                    'batch_number' => $nextBatch->batch_number,
                ]);
            }

            return true;
        }

        Log::info("No active batches found for Product #{$productId} to update prices");
        return false;
    }

    /**
     * Get current active batch prices for a product
     */
    public static function getCurrentBatchPrices($productId)
    {
        $batch = ProductBatch::where('product_id', $productId)
            ->where('status', 'active')
            ->where('remaining_quantity', '>', 0)
            ->orderBy('received_date', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        if ($batch) {
            return [
                'supplier_price' => $batch->supplier_price,
                'selling_price' => $batch->selling_price,
                'batch_number' => $batch->batch_number,
                'remaining_quantity' => $batch->remaining_quantity,
            ];
        }

        return null;
    }

    /**
     * Check available stock across all active batches
     */
    public static function getAvailableStock($productId)
    {
        return ProductBatch::where('product_id', $productId)
            ->where('status', 'active')
            ->where('remaining_quantity', '>', 0)
            ->sum('remaining_quantity');
    }

    /**
     * Get batch details for a product
     */
    public static function getBatchDetails($productId)
    {
        return ProductBatch::where('product_id', $productId)
            ->where('status', 'active')
            ->where('remaining_quantity', '>', 0)
            ->orderBy('received_date', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($batch) {
                return [
                    'batch_number' => $batch->batch_number,
                    'remaining_quantity' => $batch->remaining_quantity,
                    'supplier_price' => $batch->supplier_price,
                    'selling_price' => $batch->selling_price,
                    'received_date' => $batch->received_date->format('Y-m-d'),
                ];
            });
    }

    /**
     * Sync all product prices with their current active batches
     * Useful for ensuring prices reflect the current FIFO batch
     */
    public static function syncAllProductPrices()
    {
        $updatedCount = 0;

        // Get all products that have active batches
        $productsWithBatches = ProductBatch::where('status', 'active')
            ->where('remaining_quantity', '>', 0)
            ->groupBy('product_id')
            ->pluck('product_id');

        foreach ($productsWithBatches as $productId) {
            if (self::updateMainPrices($productId)) {
                $updatedCount++;
            }
        }

        Log::info("Synced product prices with current batches", [
            'products_updated' => $updatedCount,
            'total_products_checked' => $productsWithBatches->count(),
        ]);

        return [
            'success' => true,
            'updated' => $updatedCount,
            'checked' => $productsWithBatches->count(),
        ];
    }
}

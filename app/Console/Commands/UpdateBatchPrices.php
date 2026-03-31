<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductDetail;
use App\Models\ProductBatch;
use App\Services\FIFOStockService;

class UpdateBatchPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:update-prices {product_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update product prices based on oldest active batch (FIFO)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $productId = $this->argument('product_id');

        if ($productId) {
            // Update specific product
            $this->info("Updating prices for Product #{$productId}...");
            $result = FIFOStockService::updateMainPrices($productId);

            if ($result) {
                $this->info("✓ Prices updated successfully!");
            } else {
                $this->warn("No active batches found to update prices.");
            }
        } else {
            // Update all products with batches
            $this->info("Updating prices for all products with batches...");

            $products = ProductDetail::whereHas('batches', function ($query) {
                $query->where('status', 'active')
                    ->where('remaining_quantity', '>', 0);
            })->get();

            $this->info("Found {$products->count()} products with active batches.");

            $updated = 0;
            $skipped = 0;

            foreach ($products as $product) {
                // Get current prices
                $oldSupplierPrice = $product->price ? $product->price->supplier_price : 0;
                $oldSellingPrice = $product->price ? $product->price->selling_price : 0;

                $result = FIFOStockService::updateMainPrices($product->id);

                if ($result) {
                    // Get updated prices
                    $product->refresh();
                    $newSupplierPrice = $product->price ? $product->price->supplier_price : 0;
                    $newSellingPrice = $product->price ? $product->price->selling_price : 0;

                    $updated++;
                    $this->info("✓ Updated Product #{$product->id} ({$product->name})");
                    $this->line("  Supplier: Rs.{$oldSupplierPrice} → Rs.{$newSupplierPrice}");
                    $this->line("  Selling:  Rs.{$oldSellingPrice} → Rs.{$newSellingPrice}");
                } else {
                    $skipped++;
                    $this->warn("⊘ Skipped Product #{$product->id} ({$product->name})");
                }
            }

            $this->info("\n=================================");
            $this->info("Update completed!");
            $this->info("Updated: {$updated} products");
            $this->info("Skipped: {$skipped} products");
            $this->info("=================================");
        }

        return Command::SUCCESS;
    }
}

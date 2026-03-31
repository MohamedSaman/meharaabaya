<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductDetail;
use App\Models\ProductStock;
use App\Models\ProductBatch;
use Illuminate\Support\Facades\DB;

class MigrateExistingStockToBatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:migrate-to-batches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing product stock to batch system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of existing stock to batches...');

        $products = ProductDetail::with(['stock', 'price'])
            ->whereHas('stock', function ($query) {
                $query->where('available_stock', '>', 0);
            })
            ->get();

        $this->info("Found {$products->count()} products with available stock.");

        $migratedCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($products as $product) {
                // Check if product already has batches
                $existingBatches = ProductBatch::where('product_id', $product->id)
                    ->where('remaining_quantity', '>', 0)
                    ->count();

                if ($existingBatches > 0) {
                    $this->warn("Product #{$product->id} ({$product->name}) already has batches. Skipping...");
                    $skippedCount++;
                    continue;
                }

                $stock = $product->stock;
                $price = $product->price;

                // Get prices or use defaults
                $supplierPrice = $price ? $price->supplier_price : 0;
                $sellingPrice = $price ? $price->selling_price : 0;

                // Create initial batch for existing stock
                $batchNumber = ProductBatch::generateBatchNumber($product->id);

                ProductBatch::create([
                    'product_id' => $product->id,
                    'batch_number' => $batchNumber,
                    'purchase_order_id' => null,
                    'supplier_price' => $supplierPrice,
                    'selling_price' => $sellingPrice,
                    'quantity' => $stock->available_stock,
                    'remaining_quantity' => $stock->available_stock,
                    'received_date' => now(),
                    'status' => 'active',
                ]);

                $this->info("✓ Migrated Product #{$product->id} ({$product->name}) - Qty: {$stock->available_stock}");
                $migratedCount++;
            }

            DB::commit();

            $this->info("\n=================================");
            $this->info("Migration completed successfully!");
            $this->info("Migrated: {$migratedCount} products");
            $this->info("Skipped: {$skippedCount} products");
            $this->info("=================================");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Migration failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

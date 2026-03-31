<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'model',
        'image',
        'description',
        'barcode',
        'status',
        'brand_id',
        'category_id',
        'supplier_id',
    ];

    public function price(): HasOne
    {
        return $this->hasOne(ProductPrice::class, 'product_id');
    }

    public function stock(): HasOne
    {
        return $this->hasOne(ProductStock::class, 'product_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(BrandList::class, 'brand_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryList::class, 'category_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(ProductSupplier::class, 'supplier_id');
    }
    public function returns()
    {
        return $this->hasMany(ReturnsProduct::class, 'product_id');
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class, 'product_id');
    }

    public function activeBatches()
    {
        return $this->hasMany(ProductBatch::class, 'product_id')
            ->where('status', 'active')
            ->where('remaining_quantity', '>', 0)
            ->orderBy('received_date', 'asc')
            ->orderBy('id', 'asc');
    }

    public function detail()
    {
        return $this->hasOne(ProductDetail::class, 'code');
    }

    /**
     * Get the product image URL or default image
     *
     * @return string
     */
    public function getImageAttribute($value)
    {
        // If image exists, return it; otherwise return default image path
        return $value ?: 'images/product.jpg';
    }

    /**
     * Update product prices from the current active batch (FIFO)
     * This ensures prices reflect the oldest available batch
     *
     * @return bool
     */
    public function updatePricesFromActiveBatch()
    {
        // Get the oldest active batch (FIFO)
        $activeBatch = $this->activeBatches()->first();

        if (!$activeBatch) {
            // No active batches - keep existing prices or set to zero
            return false;
        }

        // Update product price table with the active batch prices
        $priceRecord = $this->price;

        if ($priceRecord) {
            $priceRecord->supplier_price = $activeBatch->supplier_price;
            $priceRecord->selling_price = $activeBatch->selling_price;
            $priceRecord->save();
        } else {
            // Create price record if it doesn't exist
            ProductPrice::create([
                'product_id' => $this->id,
                'supplier_price' => $activeBatch->supplier_price,
                'selling_price' => $activeBatch->selling_price,
                'discount_price' => 0,
            ]);
        }

        return true;
    }

    /**
     * Get current batch prices (FIFO - oldest active batch)
     *
     * @return array
     */
    public function getCurrentBatchPrices()
    {
        $activeBatch = $this->activeBatches()->first();

        if (!$activeBatch) {
            // Return current prices if no active batch
            return [
                'supplier_price' => $this->price->supplier_price ?? 0,
                'selling_price' => $this->price->selling_price ?? 0,
            ];
        }

        return [
            'supplier_price' => $activeBatch->supplier_price,
            'selling_price' => $activeBatch->selling_price,
            'batch_number' => $activeBatch->batch_number,
            'remaining_quantity' => $activeBatch->remaining_quantity,
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStock extends Model
{
    use HasFactory;

    protected $fillable = [ 'product_id', 'available_stock',  'damage_stock', 'total_stock','sold_count','restocked_quantity',];

    /**
     * Get the Product that owns this stock information
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductDetail::class, 'product_id');
    }
    
    /**
     * Update total and available stock automatically
     */
    public function updateTotals()
    {
        $this->total_stock = $this->available_stock + $this->damage_stock;
        $this->available_stock = $this->available_stock;
        $this->save();
    }
    public function detail()
{
    return $this->hasOne(ProductDetail::class, 'code');
}

}

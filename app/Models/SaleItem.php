<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_code',
        'product_name',
        'product_model',
        'quantity',
        'unit_price',
        'discount_per_unit',
        'total_discount',
        'total'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount_per_unit' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(ProductDetail::class, 'product_id');
    }
}
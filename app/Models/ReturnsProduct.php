<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnsProduct extends Model
{
    use HasFactory;
    protected $fillable = [
        'sale_id',
        'product_id',
        'return_quantity',
        'selling_price',
        'total_amount',
        'notes',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductDetail::class, 'product_id');
    }
}

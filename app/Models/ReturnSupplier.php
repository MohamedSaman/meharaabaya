<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnSupplier extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'return_quantity',
        'unit_price',
        'total_amount',
        'return_reason',
        'notes',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductDetail::class, 'product_id');
    }

    public function supplier()
    {
        return $this->hasOneThrough(ProductSupplier::class, PurchaseOrder::class, 'id', 'id', 'purchase_order_id', 'supplier_id');
    }
}
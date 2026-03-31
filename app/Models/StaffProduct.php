<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductDetail;

class StaffProduct extends Model
{
    // Specify the table if it does not follow Laravel conventions
    protected $table = 'staff_products';

    // Fillable fields for mass assignment
    protected $fillable = [
        'product_id',     // foreign key to product_details
        'staff_id',
        'quantity',
        'price',
        // add other fields here
    ];

    /**
     * Relationship: StaffProduct belongs to one ProductDetail
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productDetail()
    {
        // 'product_id' is the foreign key in staff_products table
        // pointing to the 'id' of the product_details table
        return $this->belongsTo(ProductDetail::class, 'product_id');
    }
    public function product()
    {
        return $this->belongsTo(ProductDetail::class, 'product_id');
    }
    public function staffSale()
    {
        return $this->belongsTo(StaffSale::class, 'staff_id');
    }

    /**
     * Get the total price for the product
     *
     * @return float
     */
    public function getTotalPriceAttribute()
    {
        return $this->quantity * $this->price;
    }   
}

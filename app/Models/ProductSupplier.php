<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductSupplier extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'businessname', 'contact', 'address', 'email', 'phone', 'status', 'notes', 'overpayment'];

    protected $casts = [
        'overpayment' => 'decimal:2',
    ];

    public function detail()
    {
        return $this->hasOne(ProductDetail::class, 'code');
    }
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }
    public function orders()
    {
        return $this->hasMany(\App\Models\PurchaseOrder::class, 'supplier_id');
    }

    /**
     * Add overpayment credit to supplier
     */
    public function addOverpayment($amount)
    {
        $this->overpayment += $amount;
        $this->save();
    }

    /**
     * Use overpayment credit from supplier
     */
    public function useOverpayment($amount)
    {
        $usedAmount = min($this->overpayment, $amount);
        $this->overpayment -= $usedAmount;
        $this->save();
        return $usedAmount;
    }

    /**
     * Get available overpayment credit
     */
    public function getAvailableOverpayment()
    {
        return $this->overpayment ?? 0;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAllocation extends Model
{
    use HasFactory;
    protected $fillable = [
        'payment_id',
        'sale_id',
        'allocated_amount',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}

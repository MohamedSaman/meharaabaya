<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestorTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'investor_id',
        'type',
        'amount',
        'transaction_date',
        'reference',
        'description',
        'payment_method',
        'cheque_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function investor()
    {
        return $this->belongsTo(Investor::class);
    }

    public function cheque()
    {
        return $this->belongsTo(Cheque::class);
    }
}

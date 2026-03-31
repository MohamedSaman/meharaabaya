<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class loans extends Model
{
    use HasFactory;

    protected $table = 'loans';
    protected $primaryKey = 'loan_id';

    protected $fillable = [
        'user_id',
        'loan_amount',
        'interest_rate',
        'start_date',
        'term_month',
        'remaining_balance',
        'status',
        'monthly_payment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashInHand extends Model
{
    use HasFactory;

    protected $table = 'cash_in_hands';

    protected $fillable = [
        'key',
        'value',
    ];

    // Optionally: Automatically cast value to integer
    protected $casts = [
        'value' => 'integer',
    ];
}

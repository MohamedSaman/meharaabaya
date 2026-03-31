<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'expense_type',
        'amount',
        'date',
        'status',
        'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'total_quantity',
        'total_value',
        'sold_quantity',
        'sold_value',
        'status',
    ];

    // Optional: define relationship with User (admin)
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}

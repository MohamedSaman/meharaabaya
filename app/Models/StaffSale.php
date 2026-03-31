<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffSale extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'staff_id', 
        'admin_id',
        'total_quantity',
        'total_value',
        'sold_quantity',
        'sold_value',
        'status',
    ];
    
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
    
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
    
    public function products()
    {
        return $this->hasMany(StaffProduct::class);
    }
}

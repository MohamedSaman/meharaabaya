<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;

    // Set primary key
    protected $primaryKey = 'user_details_id';

    // Mass assignable fields
    protected $fillable = [
        'user_id',
        'dob',
        'age',
        'nic_num',
        'address',
        'work_role',
        'work_type',
        'department',
        'gender',
        'join_date',
        'fingerprint_id',
        'allowance',
        'basic_salary',
        'user_image',
        'description',
        'status',
    ];

    // Cast JSON fields
    protected $casts = [
        'allowance' => 'array',
        'dob' => 'date',
        'join_date' => 'date',
    ];

    // Relationship: UserDetail belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: UserDetail can have many attendances
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id', 'user_id');
    }
}

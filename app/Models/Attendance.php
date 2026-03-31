<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    // Set the primary key
    protected $primaryKey = 'attendance_id';

    // If primary key is not auto-incrementing or not an integer, you can specify:
    // public $incrementing = true;
    // protected $keyType = 'int';

    // Mass assignable fields
    protected $fillable = [
        'user_id',
        'fingerprint_id',
        'date',
        'check_in',
        'break_start',
        'break_end',
        'check_out',
        'time_worked',
        'late_hours',
        'over_time',
        'status',
        'present_status',
        'description',
    ];

    // Define relationship to User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

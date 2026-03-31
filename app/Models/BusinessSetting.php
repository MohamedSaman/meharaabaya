<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'tax_id',
        'registration_no',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'email',
        'website',
        'currency',
        'tax_rate',
        'logo_url',
        'primary_color',
        'secondary_color',
        'template',
        'footer_text',
        'terms_conditions',
    ];
}

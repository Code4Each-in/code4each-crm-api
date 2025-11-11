<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBilling extends Model
{
    use HasFactory;

    protected $table = 'user_billing';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'zip_code',
        'country',
        'agency_id',
        'website_id',
        'plan_id',
    ];
}

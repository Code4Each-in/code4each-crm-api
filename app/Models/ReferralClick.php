<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralClick extends Model
{
    use HasFactory;

    protected $table = 'referral_clicks';

    protected $fillable = [
        'referral_code',
        'ip_address',
    ];
}

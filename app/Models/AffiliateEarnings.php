<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateEarnings extends Model
{
    use HasFactory;

    protected $table = 'affiliate_earnings';

    protected $fillable = [
        'agent_id',
        'referral_code',
        'amount',
    ];
}

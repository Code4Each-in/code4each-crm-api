<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateWithdrawals extends Model
{
    use HasFactory;

    protected $table = 'affiliate_withdrawals';

    protected $fillable = [
        'agent_id',
        'amount',
        'status',
        'account_details_id',
    ];
}

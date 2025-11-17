<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateBankAccountDetails extends Model
{
    use HasFactory;

    protected $table = 'affiliate_bank_account_details';

    protected $fillable = [
        'agent_id',
        'account_holder_name',
        'card_number',
        'expiry_month',
        'expiry_year',
        'cvv',
    ];
}

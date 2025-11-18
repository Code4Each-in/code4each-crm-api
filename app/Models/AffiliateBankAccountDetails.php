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
        'account_name',
        'bank_name',
        'account_number',
        'ifsc',
    ];
}

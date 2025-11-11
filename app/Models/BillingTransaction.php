<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingTransaction extends Model
{
    use HasFactory;
    
    protected $table = 'billing_transaction';

    protected $fillable = [
        'user_billing_id',
        'plan_id',
        'user_id',
        'status',
        'agency_id',
        'website_id',
        'payment_id',
        'order_id',
        'amount',
        'payment_details',
        'card_details',
    ];
}

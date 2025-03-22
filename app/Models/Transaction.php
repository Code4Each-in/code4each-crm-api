<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transaction';

    protected $fillable = [
        'payment_id',
        'order_id',
        'plan_id',
        'user_id',
        'website_id',
        'agency_id',
        'amount',
        'signature',
        'is_refunded',
        'refunded_amount',
    ];

}

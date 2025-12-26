<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'account_id',
        'name',
        'email',
        'avatar',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'raw_data',
    ];

}

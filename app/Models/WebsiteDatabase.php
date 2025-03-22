<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebsiteDatabase extends Model
{
    use HasFactory;

    protected $table = 'website_databases';

    protected $fillable = [
        'id',
        'website_id',
        'agency_id',
        'name',
        'username',
        'password',
        'website_domain',
        'updated_at'
    ];

}

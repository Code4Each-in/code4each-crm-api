<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanLog extends Model
{
    use HasFactory;
    protected $table = 'plan_log';

    protected $fillable = [
        'agency_id',
        'user_id', 
        'website_id',
        'plan_id',
    ];      
}

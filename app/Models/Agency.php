<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agency extends Model
{
    use HasFactory, SoftDeletes;

    public function users()
    {
        return $this->hasMany(User::class, 'agency_id', 'id');
    }

    public function agencyWebsites()
    {
        return $this->hasMany(AgencyWebsite::class, 'agency_id', 'id');
    }

}

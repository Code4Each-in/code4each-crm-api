<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Websites extends Model
{
    use HasFactory;

    public function agencyWebsiteDetail()
    {
        return $this->hasMany(AgencyWebsite::class, 'website_id');
    }

}

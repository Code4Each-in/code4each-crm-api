<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Websites extends Model
{
    use HasFactory;

    protected $hidden = [
        'admin_username',
        'admin_password',
    ];

    public function agencyWebsiteDetail()
    {
        return $this->hasOne(AgencyWebsite::class, 'website_id');
    }

}

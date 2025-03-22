<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgencyWebsite extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'website_category_id',
        'others_category_name',
        'business_name',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'pin',
        'description',
        'logo',
        'agency_id',
        'status',
        'website_id',
        'created_by',
    ];

    public function websiteCategory()
    {
        return $this->belongsTo(WebsiteCategory::class, 'website_category_id');
    }

    public function websiteDetail()
    {
        return $this->belongsTo(Websites::class, 'website_id');
    }
    public function websiteUser()
    {
        return $this->belongsTo(User::class, 'created_by','id');
    }

    public function agency()
    {
        return $this->hasOne(Agency::class, 'agency_id','id');
    }
}

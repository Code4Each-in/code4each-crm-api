<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteTemplate extends Model
{
    use HasFactory;
    protected $table = 'website_templates';
    protected $fillable = [
        'template_name',
        'category_id',
        'preview_link',
        'featured_image',
        'status',
    ];

    public function components()
    {
        return $this->hasMany(WebsiteTemplateComponent::class, 'template_id', 'id');
    }

    public function componentDetails()
    {
        return $this->hasManyThrough(
            Component::class,
            WebsiteTemplateComponent::class,
            'template_id',     
            'component_unique_id', 
            'id',             
            'component_unique_id' 
        );
    }
}

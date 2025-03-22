<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteTemplateComponent extends Model
{
    use HasFactory;
    protected $table = 'website_templates_components';
    protected $fillable = [
        'template_id',
        'component_unique_id',
        'position',
    ];
    
    public function component()
    {
        return $this->belongsTo(Component::class, 'component_unique_id', 'component_unique_id');
    }

    public function componentDetail()
    {
            return $this->hasOne(Component::class, 'component_unique_id', 'component_unique_id');
    
    }
}

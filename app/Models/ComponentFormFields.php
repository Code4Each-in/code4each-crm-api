<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComponentFormFields extends Model
{
    use HasFactory;
    protected $table = 'component_form_fields';
    protected $fillable = [
        'component_id',
        'area_id',
        'parent_id',
        'field_name',
        'field_type',
        'field_position',
        'default_value',
        'is_multiple_image',
        'meta_key1',
        'meta_key2',
    ];

    public function parent()
    {
        return $this->belongsTo(ComponentFormFields::class, 'parent_id');
    }

    // Get Sub form fields for the form field
    public function children()
    {
        return $this->hasMany(ComponentFormFields::class, 'parent_id');
    }

    public function component()
    {
        return $this->belongsTo(Component::class, 'component_id');
    }

}

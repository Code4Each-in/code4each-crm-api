<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Component extends Model 
{
    use HasFactory;
    protected $table = 'components_crm';

    protected $fillable = ['component_name','path','type','category','preview','status'];

    public function dependencies()
    {
        return $this->hasMany(ComponentDependency::class, 'component_id');
    }
    public function formFields()
    {
        return $this->hasMany(ComponentFormFields::class, 'component_id')->orderBy('field_position');
    }

    public function componentAreas()
    {
        return $this->hasMany(ComponentArea::class);
    }
}

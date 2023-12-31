<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    use HasFactory;
    protected $table = 'components_crm';

    protected $fillable = ['component_name','path','type','category','preview'];

    public function dependencies()
    {
        return $this->hasMany(ComponentDependency::class, 'component_id');
    }
}

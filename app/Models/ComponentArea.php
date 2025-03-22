<?php

namespace App\Models; 

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComponentArea extends Model
{
    use HasFactory;
    protected $table = 'component_area';

    protected $fillable = ['component_id','area_name','x_axis','y_axis','area_width', 'area_height'];

    public function component()
    {
        return $this->belongsTo(Component::class);
    }

}

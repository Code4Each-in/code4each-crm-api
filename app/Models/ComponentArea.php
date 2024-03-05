<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComponentArea extends Model
{
    use HasFactory;
    protected $table = 'component_area';

    protected $fillable = ['component_id','areaCount','area_name','x_axis','y_axis'];

}

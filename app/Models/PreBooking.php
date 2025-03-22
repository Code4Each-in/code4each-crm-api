<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class PreBooking extends Model
{
    use HasFactory,Notifiable;

    protected $table = 'user_notify_emails';
    protected $fillable = ['email'];

}

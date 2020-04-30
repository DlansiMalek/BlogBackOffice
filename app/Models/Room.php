<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'Room';
    protected $primaryKey = 'room_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['moderator_token','invitee_token','admin_id'];
}

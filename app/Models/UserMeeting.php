<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMeeting extends Model
{
    public $timestamps = true;
    protected $table = 'User_Meeting';
    protected $primaryKey = 'user_meeting_id';
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = ['status','user_sender_id', 'user_receiver_id','meeting_id','user_canceler'];

    function meeting() {
        return $this->belongsTo(Meeting::class,'meeting_id','meeting_id');
    }
}
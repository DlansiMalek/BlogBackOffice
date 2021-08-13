<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class Meeting extends Model
{

    protected $table = 'Meeting';
    protected $primaryKey = 'meeting_id';
    protected $fillable = ['name', 'start_date', 'end_date'];
    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];

    public function user_meeting()
    {
        return $this->hasMany(UserMeeting::class, 'meeting_id', 'meeting_id');
    }
}

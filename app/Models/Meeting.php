<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class Meeting extends Model
{

    protected $table = 'Meeting';
    protected $primaryKey = 'meeting_id';
    protected $fillable = ['name', 'start_date', 'end_date','user_canceler'];
    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];

    public function user_meeting()
    {
        return $this->hasMany(UserMeeting::class, 'meeting_id', 'meeting_id');
    }
    public function meeting_table()
    {
        return $this->hasOne(MeetingTable::class, 'meeting_table_id', 'meeting_table_id');
    }
}

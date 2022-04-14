<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class MeetingDates extends Model
{

    protected $table = 'Meeting_Dates';
    protected $primaryKey = 'meeting_dates_id';
    protected $fillable = ['start_date, end_date, congress_id'];
    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];

  
}

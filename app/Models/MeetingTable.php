<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class MeetingTable extends Model
{

    protected $table = 'Meeting_Table';
    protected $primaryKey = 'meeting_table_id';
    protected $fillable = ['label'];
    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];

    function meetings() {
        return $this->hasMany(Meeting::class,'meeting_table_id','meeting_table_id');
    }

}

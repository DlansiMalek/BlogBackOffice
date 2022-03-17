<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingEvaluation extends Model
{
    protected $table = 'Meeting_Evaluation';
    protected $primaryKey = 'meeting_evaluation_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['comment','note','user_id','meeting_id'];

    
}

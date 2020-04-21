<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $table = 'Submission';
    protected $primaryKey = 'submission_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['title', 'type', 'prez_type', 'description', 'global_note', 'status'];
    
    function Authors(){
        return $this->hasMany('App\Models\Author','submission_id','submission_id');
     }
     function Resources(){
        return $this->belongsToMany('App\Models\Resource','Resource_Submission','submission_id','resource_id');
     }
     function Congress(){
        return $this->belongsTo('App\Models\Congress','congress_id','congress_id');
     }
}


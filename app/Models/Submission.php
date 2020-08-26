<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $table = 'Submission';
    protected $primaryKey = 'submission_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['title', 'type', 'prez_type', 'description', 'global_note', 'status','upload_file_code'];


    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function communicationType() {
        return $this->belongsTo('App\Models\CommunicationType','communication_type_id');
    }

    public function theme()
    {
        return $this->belongsTo('App\Models\Theme', 'theme_id');
    }

    public function submissions_evaluations()
    {
        return $this->hasMany('App\Models\SubmissionEvaluation', 'submission_id');
    }

    function authors()
    {
        return $this->hasMany('App\Models\Author', 'submission_id', 'submission_id');
    }

    function resources()
    {
        return $this->belongsToMany('App\Models\Resource', 'Resource_Submission', 'submission_id', 'resource_id');
    }

    function congress()
    {
        return $this->belongsTo('App\Models\Congress', 'congress_id', 'congress_id');
    }
}


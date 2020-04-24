<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $table = 'Submission';
    protected $primaryKey = 'submission_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['title', 'type', 'prez_type', 'description', 'global_note', 'status'];



    public function user() {
        return $this->belongsTo('App\Models\User','user_id');
    }

    public function authors() {
        return $this->hasMany('App\Models\Author','submission_id');
    }

    public function theme() {
        return $this->belongsTo('App\Models\Theme','theme_id');
    }
    public function submissions_evaluations() {
        return $this->hasMany('App\Models\SubmissionEvaluation','submission_id');
    }
}

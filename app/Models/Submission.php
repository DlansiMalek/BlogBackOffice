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
        return $this->belongsTo('App\Models\User','submission_id');
    }

    public function author() {
        return $this->belongsTo('App\Models\Author','submission_id');
    }

    public function theme() {
        return $this->belongsTo('App\Models\Theme','submission_id');
    }
    public function submission_evaluation() {
        return $this->hasMany('App\Models\SubmissionEvaluation','submission_id');
    }
}

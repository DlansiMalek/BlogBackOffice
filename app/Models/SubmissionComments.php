<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SubmissionComments extends Model {
    protected $table = 'Submission_Comments';
    protected $primaryKey = 'submission_comments_id';
    protected $fillable = ['description','submission_id'];
    public $timestamps = false;

    public function submission() {
        return $this->belongsTo('App\Models\Submission','submission_id');
    }
}
<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SubmissionEvaluation extends Model {

    protected $table = 'Submission_Evaluation';
    protected $primaryKey = 'submission_evaluation_id';
    protected $fillable = ['submission_id','admin_id','note','communication_type_id','theme_id'];
    public $timestamps = false;

    public function submission() {
        return $this->belongsTo('App\Models\Submission','submission_id');
    }
    public function  evaluator() {
        return $this->belongsTo('App\Models\Admin','admin_id');

    }

}
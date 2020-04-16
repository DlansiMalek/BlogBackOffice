<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SubmissionEvaluation extends Model {

    protected $table = 'Submission_Evaluation';
    protected $primaryKey = 'submission_evaluation_id';
    protected $fillable = ['submission_id','admin_id','note'];
    public $timestamps = false;
}
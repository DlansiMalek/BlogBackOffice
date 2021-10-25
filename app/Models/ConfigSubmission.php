<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigSubmission extends Model
{

    protected $table = 'Config_Submission';
    protected $primaryKey = 'config_submission_id';
    protected $fillable = ['congress_id', 'max_words',	'start_submission_date',	'end_submission_date', 'show_file_upload'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;

}
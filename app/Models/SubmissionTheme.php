<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionTheme extends Model
{
    public $timestamps = true;
    protected $table = 'Submission_Theme';
    protected $primaryKey = 'submission_theme_id';
    protected $fillable = ['theme_id','congress_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    function theme()
    {
        return  $this->belongsTo('App\Models\Theme','theme_id','theme_id');
    }
    function congress()
    {
         return  $this->belongsTo('App\Models\Congress','congress_id','congress_id');
    }
}

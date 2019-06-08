<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackQuestion extends Model
{
    public $timestamps = true;
    protected $table = 'Feedback_Question';
    protected $primaryKey = 'feedback_question_id';
    protected $fillable = ['question','congress_id','access_id', 'isText'];
    protected $dates = ['created_at', 'updated_at'];

    public function values(){
        return $this->hasMany("App\Models\FeedbackValue","feedback_question_id","feedback_question_id")->orderBy('order');
    }

    public function access(){
        return $this->belongsTo('App\Models\Access','access_id','access_id');
    }

    public function congress(){
        return $this->belongsTo('App\Models\Congress','congress_id','congress_id');
    }

    public function responses(){
        return $this->hasMany("App\Models\Feedback_Response", 'feedback_question_id','feedback_question_id');
    }

}
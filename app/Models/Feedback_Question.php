<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback_Question extends Model
{
    public $timestamps = true;
    protected $table = 'Feedback_Question';
    protected $primaryKey = 'feedback_question_id';
    protected $fillable = ['label','congress_id','feedback_question_type_id', 'max_responses','order'];
    protected $dates = ['created_at', 'updated_at'];

    public function type(){
        return $this->hasOne("App\Models\Feedback_Question_Type","feedback_question_type_id","feedback_question_type_id");
    }

    public function values(){
        return $this->hasMany("App\Models\Feedback_Question_Value","feedback_question_id","feedback_question_id")->orderBy('order');
    }

    public function responses(){
        return $this->hasMany("App\Models\Feedback_Response", 'feedback_question_id','feedback_question_id');
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackValue extends Model
{
    public $timestamps = true;
    protected $table = 'Feedback_Value';
    protected $primaryKey = 'feedback_value_id';
    protected $fillable = ['value','feedback_question_id','order'];
    protected $dates = ['created_at', 'updated_at'];

    public function responses(){
        return $this->hasMany('App\Models\FeedbackResponse','feedback_question_value_id','feedback_question_value_id');
    }

}
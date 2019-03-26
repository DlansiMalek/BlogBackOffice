<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback_Question_Value extends Model
{
    public $timestamps = true;
    protected $table = 'Feedback_Question_Value';
    protected $primaryKey = 'feedback_question_value_id';
    protected $fillable = ['value','feedback_question_id'];
    protected $dates = ['created_at', 'updated_at'];

}
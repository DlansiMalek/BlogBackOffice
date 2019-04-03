<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback_Response extends Model
{
    public $timestamps = true;
    protected $table = 'Feedback_Response';
    protected $primaryKey = 'feedback_response_id';
    protected $fillable = ['text','feedback_question_value_id','user_id', 'feedback_question_id'];
    protected $dates = ['created_at', 'updated_at'];

}
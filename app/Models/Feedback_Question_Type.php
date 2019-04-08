<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback_Question_Type extends Model
{
    public $timestamps = true;
    protected $table = 'Feedback_Question_Type';
    protected $primaryKey = 'feedback_question_type_id';
    protected $fillable = ['name', "display_name"];
    protected $dates = ['created_at', 'updated_at'];

}
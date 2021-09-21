<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionReference extends Model
{
    protected $table = 'Question_Reference';
    protected $primaryKey = 'question_reference_id';
    protected $fillable = ['reference_id','form_input_id']; 

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function reference() {
        return $this->hasOne("App\Models\FormInput", "form_input_id", "reference_id");
    }

    public function response_reference() {
        return $this->hasMany("App\Models\ResponseReference", "question_reference_id", "question_reference_id");
    }
}

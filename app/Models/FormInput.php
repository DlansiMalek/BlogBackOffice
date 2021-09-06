<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormInput extends Model
{
    public $timestamps = true;
    protected $table = 'Form_Input';
    protected $primaryKey = 'form_input_id';
    protected $fillable = ['label', 'congress_id', 'form_input_type_id', 'required'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function type()
    {
        return $this->hasOne("App\Models\FormInputType", "form_input_type_id", "form_input_type_id");
    }

    public function values()
    {
        return $this->hasMany("App\Models\FormInputValue", "form_input_id", "form_input_id");
    }

    public function question_reference() {
        return $this->hasMany("App\Models\QuestionReference", "form_input_id", "form_input_id");
    }

}
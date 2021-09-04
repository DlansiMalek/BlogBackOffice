<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponseReference extends Model
{
    protected $table = 'Response_Reference';
    protected $primaryKey = 'response_reference_id';
    protected $fillable = ['form_input_value_id', 'question_reference_id'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function value() {
        return $this->hasOne("App\Models\FormInputValue", "form_input_value_id", "form_input_value_id");
    }
}

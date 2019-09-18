<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormInputResponse extends Model
{
    public $timestamps = true;
    protected $table = 'Form_Input_Response';
    protected $primaryKey = 'form_input_response_id';
    protected $fillable = ['response', 'user_id', 'form_input_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    function values()
    {
        return $this->hasMany(ResponseValue::class, 'form_input_response_id', 'form_input_response_id');
    }

    function form_input()
    {
        return $this->hasOne('App\Models\FormInput', 'form_input_id', 'form_input_id');
    }

}
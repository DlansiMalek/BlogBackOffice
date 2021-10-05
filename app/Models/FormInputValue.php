<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormInputValue extends Model
{
    public $timestamps = true;
    protected $table = 'Form_Input_Value';
    protected $primaryKey = 'form_input_value_id';
    protected $fillable = ['value','form_input_id'];
    protected $dates = ['created_at', 'updated_at','deleted_at'];


}
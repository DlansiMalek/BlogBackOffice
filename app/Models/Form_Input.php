<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form_Input extends Model
{
    public $timestamps = true;
    protected $table = 'Form_Input';
    protected $primaryKey = 'form_input_id';
    protected $fillable = ['label','congress_id','type_id'];
    protected $dates = ['created_at', 'updated_at'];

    public function form_input_type(){
        return $this->hasOne("App\Models\Form_Input_Type","form_input_type_id","form_input_type_id");
    }

    public function form_input_values(){
        return $this->hasMany("App\Models\Form_Input_Value","form_input_id","form_input_id");
    }

}
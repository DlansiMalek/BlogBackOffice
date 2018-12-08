<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form_Input_Reponse extends Model
{
    public $timestamps = true;
    protected $table = 'Form_Input_Reponse';
    protected $primaryKey = 'form_input_reponse_id';
    protected $fillable = ['reponse','user_id','form_input_id'];
    protected $dates = ['created_at', 'updated_at'];

    function values()
    {
        return $this->belongsToMany('App\Models\Form_Input_Value', 'Reponse_Value', 'form_input_reponse_id', 'form_input_value_id');
    }

    function form_input(){
        return $this->hasOne('App\Models\Form_Input', 'form_input_id','form_input_id');
    }

}
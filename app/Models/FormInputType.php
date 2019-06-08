<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormInputType extends Model
{
    public $timestamps = true;
    protected $table = 'Form_Input_Type';
    protected $primaryKey = 'form_input_type_id';
    protected $fillable = ['name', "display_name"];
}
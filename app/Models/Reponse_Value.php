<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reponse_Value extends Model
{
    public $timestamps = true;
    protected $table = 'Reponse_Value';
    protected $primaryKey = 'reponse_value_id';
    protected $fillable = ['form_input_value_id','form_input_reponse_id'];
    protected $dates = ['created_at', 'updated_at'];

}
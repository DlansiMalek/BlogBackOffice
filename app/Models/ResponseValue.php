<?php
/**
 * Created by IntelliJ IDEA.
 * User: ABBES
 * Date: 29/06/2019
 * Time: 13:17
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ResponseValue extends Model
{
    public $timestamps = true;
    protected $table = 'Response_Value';
    protected $primaryKey = 'response_value_id';
    protected $fillable = ['response', 'form_input_response_id', 'form_input_value_id'];
    protected $dates = ['created_at', 'updated_at'];

}
<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Add_Info_Value extends Model
{
    protected $table = 'Add_Info_Value';
    protected $primaryKey = 'add_info_value_id';
    protected $fillable = ['value', 'add_info_id', 'user_id'];


    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];


}
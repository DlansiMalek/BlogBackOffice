<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Add_Info extends Model
{
    protected $table = 'Add_Info';
    protected $primaryKey = 'add_info_id';
    protected $fillable = ['type_info_id', 'congress_id'];


    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];


}
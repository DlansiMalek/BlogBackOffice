<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payement_Type extends Model
{
    protected $table = 'Payement_Type';
    protected $primaryKey = 'payement_type_id';
    protected $fillable = ['label'];


    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];


}
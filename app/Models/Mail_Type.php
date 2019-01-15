<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mail_Type extends Model
{
    public $timestamps = true;
    protected $table = 'Mail_Type';
    protected $primaryKey = 'mail_type_id';
    protected $fillable = ['name'];
    protected $dates = ['created_at', 'updated_at','deleted_at'];

}
<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Custom_Mail extends Model
{
    public $timestamps = true;
    protected $table = 'Custom_Mail';
    protected $primaryKey = 'custom_mail_id';
    protected $fillable = ['congress_id','object','template'];
    protected $dates = ['created_at', 'updated_at','deleted_at'];

}
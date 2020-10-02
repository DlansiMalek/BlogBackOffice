<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:16
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    protected $table = 'Action';
    protected $primaryKey = 'action_id';
    protected $fillable = ['key', 'value'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

}

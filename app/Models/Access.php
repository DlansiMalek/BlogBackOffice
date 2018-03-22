<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Access extends Model
{
    protected $table = 'Access';
    protected $primaryKey = 'access_id';
    protected $fillable = ['price', 'type_access_id', 'congress_id'];


    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];

    public function responsibles()
    {
        return $this->hasMany('App\Models\Admin_Access', 'access_id','access_id');
    }


}
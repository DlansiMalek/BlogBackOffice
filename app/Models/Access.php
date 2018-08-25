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
    public $timestamps = true;
    protected $table = 'Access';
    protected $primaryKey = 'access_id';
    protected $fillable = ['price', 'name', 'ponderation', 'duration', 'congress_id'];
    protected $dates = ['created_at', 'updated_at'];

    public function responsibles()
    {
        return $this->hasMany('App\Models\Admin_Access', 'access_id', 'access_id');
    }

}
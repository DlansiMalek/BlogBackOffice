<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $table = 'Badge';
    protected $primaryKey = 'badge_id';
    protected $fillable = ['badge_id_generator', 'privilege_id', 'congress_id'];


    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at','deleted_at'];



    public function privilege() {
        return  $this->belongsTo('App\Models\Privilege','privilege_id');

    }
    public function badge_param() {
        return $this->hasMany('App\Models\BadgeParams','badge_id');
    }
}
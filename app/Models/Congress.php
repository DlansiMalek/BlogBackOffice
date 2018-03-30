<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:16
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Congress extends Model
{
    protected $table = 'Congress';
    protected $primaryKey = 'congress_id';
    protected $fillable = ['name', 'date'];

    protected $dates = ['created_at', 'updated_at'];
    public $timestamps = true;


    public function responsibles()
    {
        return $this->hasMany('App\Models\Admin_Congress', "congress_id", "congress_id");
    }

    public function accesss()
    {
        return $this->hasMany('App\Models\Access', "congress_id", "congress_id");
    }

    public function add_infos()
    {
        return $this->hasMany('App\Models\Add_Info', "congress_id", "congress_id");
    }

    public function badge()
    {
        return $this->belongsTo('App\Models\Badge', 'congress_id', 'congress_id');
    }
}
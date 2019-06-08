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
    protected $fillable = ['name', 'price', 'duration', 'max_places', 'total_present_in_congress',
        'seuil', 'room', 'description', 'congress_id', 'packless',
        'start_date', 'real_start_date', 'end_date', 'parent_id'];
    protected $dates = ['created_at', 'updated_at'];


    public function participants()
    {
        return $this->belongsToMany('App\Models\User', 'User_Access', 'access_id', 'user_id')
            ->withPivot('isPresent');
    }

    // speakers
    public function speakers()
    {
        return $this->belongsToMany('App\Models\User', 'Speaker_Access', 'access_id', 'user_id');
    }

    //chair persons
    public function chairs()
    {
        return $this->belongsToMany('App\Models\User', '_AccessChair', 'access_id', 'user_id');
    }

    public function attestation()
    {
        return $this->hasOne('App\Models\Attestation_Access', 'access_id', 'access_id');
    }

    public function votes()
    {
        return $this->hasMany('App\Models\Access_Vote', 'access_id', 'access_id');
    }

    public function quiz_associations()
    {
        return $this->hasMany('App\Models\Access_Vote', 'access_id', 'access_id');
    }

    //sub-access
    public function sub_access()
    {
        return $this->hasMany('App\Models\Access', 'access_id', 'parent_id');
    }

    //topic
    public function topic()
    {
        return $this->hasOne('App\Models\Topic', 'access_id', 'access_id');
    }

    //resources
    public function resources()
    {
        return $this->hasMany('App\Models\Resource', 'access_id', 'access_id');
    }

    //access_type
    public function access_type()
    {
        return $this->hasOne('App\Models\Access_type', 'access_id', 'access_id');
    }


}
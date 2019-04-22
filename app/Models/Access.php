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
    protected $fillable = ['price', 'name', 'ponderation', 'duration', 'congress_id', 'block',
        'seuil', 'total_present_in_congress', 'intuitive','packless','seuil','max_places','theoric_start_data','theoric_end_data'];
    protected $dates = ['created_at', 'updated_at'];


    public function participants()
    {
        return $this->belongsToMany('App\Models\User', 'User_Access', 'access_id', 'user_id')
            ->withPivot('isPresent');
    }

    public function attestation()
    {
        return $this->hasOne('App\Models\Attestation_Access', 'access_id', 'access_id');
    }

    public function votes(){
        return $this->hasMany('App\Models\Access_Vote','access_id','access_id');
    }

    public function quiz_associations(){
        return $this->hasMany('App\Models\Access_Vote','access_id','access_id');
    }


}
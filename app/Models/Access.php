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
        'seuil', 'room', 'token_jitsi', 'token_jitsi_moderator', 'token_jitsi_participant', 'description', 'congress_id', 'packless',
        'start_date', 'real_start_date', 'end_date', 'parent_id', 'show_in_program',
        'show_in_register', 'with_attestation', 'is_online','banner'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];


    public function user_accesses()
    {
        return $this->hasMany(UserAccess::class, 'access_id', 'access_id')
            ->count();
    }
    public function user_accesss()
    {
        return $this->hasMany('App\Models\UserAccess', 'access_id', 'access_id');

    }

    public function participants()
    {
        return $this->belongsToMany('App\Models\User', 'User_Access', 'access_id', 'user_id')
            ->withPivot('isPresent');
    }

    // speakers
    public function speakers()
    {
        return $this->belongsToMany('App\Models\User', 'Access_Speaker', 'access_id', 'user_id');
    }

    //chair persons
    public function chairs()
    {
        return $this->belongsToMany('App\Models\User', 'Access_Chair', 'access_id', 'user_id');
    }

    public function attestations()
    {
        return $this->hasMany('App\Models\AttestationAccess', 'access_id', 'access_id');
    }

    public function votes()
    {
        return $this->hasMany('App\Models\AccessVote', 'access_id', 'access_id');
    }

    public function quiz_associations()
    {
        return $this->hasMany('App\Models\AccessVote', 'access_id', 'access_id');
    }

    //sub-access
    public function sub_accesses()
    {
        return $this->hasMany('App\Models\Access', 'parent_id', 'access_id');
    }

    //topic
    public function topic()
    {
        return $this->hasOne('App\Models\Topic', 'topic_id', 'topic_id');
    }

    //resources
    public function resources()
    {
        return $this->hasMany('App\Models\Resource', 'access_id', 'access_id');
    }

    public function type()
    {
        return $this->hasOne('App\Models\AccessType', 'access_type_id', 'access_type_id');
    }

    function packs()
    {
        return $this->belongsToMany('App\Models\Pack', 'Access_Pack', 'access_id', 'pack_id');
    }

    function tracking()
    {
        return $this->hasMany(Tracking::class, 'access_id', 'access_id');
    }

    public function speaker()
    {
        return $this->hasOne('App\Models\LPSpeaker', 'lp_speaker_id', 'lp_speaker_id');
    }

}

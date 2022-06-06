<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCongress extends Model
{
    public $timestamps = true;
    protected $table = 'User_Congress';
    protected $primaryKey = 'user_congress_id';
    protected $fillable = ['user_id', 'congress_id', 'isPresent', 'globale_score' ,'token_jitsi', 'organization_accepted', 'privilege_id', 'organization_id', 'pack_id', 'pack_admin_id','will_be_present'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'user_id');
    }

    public function privilege()
    {
        return $this->belongsTo('App\Models\Privilege', 'privilege_id', 'privilege_id');
    }

    public function organization()
    {
        return $this->belongsTo('App\Models\Organization', 'organization_id', 'organization_id');
    }

    public function congress()
    {
        return $this->belongsTo('App\Models\Congress', 'congress_id', 'congress_id');
    }

    public function pack()
    {
        return $this->belongsTo('App\Models\Pack', 'pack_id', 'pack_id');
    }
    public function tracking() {
        return $this->hasMany(Tracking::class,'user_id','user_id');
    }
}

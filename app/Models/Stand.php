<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stand extends Model
{

    public $timestamps = true;
    protected $table = 'Stand';
    protected $primaryKey = 'stand_id';
    protected $fillable = ['name', 'congress_id', 'organization_id', 'url_streaming'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    function docs()
    {
        return $this->belongsToMany(Resource::class,'Resource_Stand','stand_id','resource_id')
        ->withPivot(['version', 'file_name']);
    }
    function organization() {
        return $this->hasOne(Organization::class,'organization_id','organization_id');
    }

    function tracking() {
        return $this->hasMany(Tracking::class,'stand_id','stand_id');
    }

}



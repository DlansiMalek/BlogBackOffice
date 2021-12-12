<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stand extends Model
{

    public $timestamps = true;
    protected $table = 'Stand';
    protected $primaryKey = 'stand_id';
    protected $fillable = ['name', 'congress_id', 'organization_id', 'url_streaming', 'booth_size', 'website_link', 'fb_link', 'insta_link', 'twitter_link', 'linkedin_link', 'priority', 'primary_color', 'secondary_color','with_products',];
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
	
    function products() {
        return $this->hasMany(StandProduct::class,'stand_id','stand_id');
    }
    function stags() {
        return $this->belongsToMany(STag::class, 'Stand_Tag', 'stand_id', 'stag_id');
    }

    function faq() {
        return $this->hasMany(FAQ::class,'stand_id','stand_id');
    }

    function stand_content_file() {
        return $this->belongsToMany(StandContentConfig::class, 'Stand_Content_File', 'stand_id', 'stand_content_config_id')
            ->withPivot(['file', 'url']);
    }

    function stand_type() {
        return $this->hasOne(StandType::class,'stand_type_id','stand_type_id');
    }

    function stand_content_config() {
        return $this->hasOne(StandContentConfig::class,'stand_type_id','stand_type_id');
    }
}



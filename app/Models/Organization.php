<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    public $timestamps = true;
    protected $table = 'Organization';
    protected $primaryKey = 'organization_id';
    protected $fillable = ['name', 'description', 'mobile', 'email', 'admin_id', 'is_sponsor', 'banner', 'logo'
        , 'website_link', 'twitter_link', 'linkedin_link', 'fb_link', 'insta_link', 'montant', 'congress_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    function stands()
    {
    return $this->hasMany(Stand::class, 'organization_id', 'organization_id')->with(['docs']);
    }

    function admin()
    {
      return $this->belongsTo('App\Models\Admin', 'admin_id', 'admin_id');
    }
    function membres()
    {
      return $this->belongsToMany('App\Models\User', 'User_Congress', 'organization_id', 'user_id');
    }

    function stags() {
      return $this->belongsToMany(STag::class, 'Stand_Tag', 'stand_id', 'stag_id');
  }
}

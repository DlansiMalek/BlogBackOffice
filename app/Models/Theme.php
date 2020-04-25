<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
        protected $table = 'Theme';
        protected $primaryKey = 'theme_id';
        protected $dates = ['created_at', 'updated_at', 'deleted_at'];
        protected $fillable = ['label', 'description'];

    function congresses()
    {
        return $this->belongsToMany('App\Models\Congress', 'Congress_Theme', 'theme_id', 'congress_id');
    }
    
    function themeAdmin(){

        return $this->hasMany('App\Models\ThemeAdmin','theme_id','theme_id');
    }
}

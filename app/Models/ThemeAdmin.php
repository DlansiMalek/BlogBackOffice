<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThemeAdmin extends Model
{
    public $timestamps = true;
    protected $table = 'Theme_Admin';
    protected $primaryKey = 'theme_admin_id';
    protected $fillable = ['theme_id','admin_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    function theme()
    {
        return  $this->belongsTo('App\Models\Theme','theme_id','theme_id');
    }
    function admin()
    {
         return  $this->belongsTo('App\Models\Admin','admin_id','admin_id');
    }
}
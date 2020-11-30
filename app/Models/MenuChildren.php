<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuChildren extends Model
{
    protected $table = 'Menu_Children';
    protected $primaryKey = 'menu_children_id';
    protected $fillable = ['key', 'icon', 'url'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;

    public function menu() {
        return $this->belongsTo(Menu::class, 'menu_id', 'menu_id');
    }

    public function menu_children_offre()
    {
        return $this->hasMany(MenuChildrenOffre::class, 'menu_children_id', 'menu_children_id');
    }

    public function privilege_menu_children()
    {
        return $this->hasMany(PrivilegeMenuChildren::class,'menu_children_id', 'menu_children_id');
    }
}

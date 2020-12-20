<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'Menu';
    protected $primaryKey = 'menu_id';
    protected $fillable = ['key', 'icon'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;

    public function menu_children() {
        return $this->hasMany(MenuChildren::class, 'menu_id', 'menu_id');
    }

    public function menu_children_offre()
    {
        return $this->hasMany(MenuChildrenOffre::class, 'menu_id', 'menu_id');
    }

    public function privilege_menu_children()
    {
        return $this->hasMany(PrivilegeMenuChildren::class, 'menu_id', 'menu_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivilegeConfigChildren extends Model
{
    protected $table = 'Privilege_Config_Children';
    protected $primaryKey = 'privilege_config_children_id';
    protected $fillable = ['menu_children_id', 'privilege_config_id', 'menu_id'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;

    public function menu_children()
    {
        return $this->belongsTo(MenuChildren::class, 'menu_children_id', 'menu_children_id');
    }

    public function privilege_config()
    {
        return $this->belongsTo(PrivilegeConfig::class, 'privilege_config_id', 'privilege_config_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'menu_id');
    }
}

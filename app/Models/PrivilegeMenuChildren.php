<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivilegeMenuChildren extends Model
{
    protected $table = 'Privilege_Menu_Children';
    protected $primaryKey = 'privilege_menu_children_id';
    protected $fillable = ['menu_children_id', 'privilege_id', 'menu_id'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;

    public function menu_children()
    {
        return $this->belongsTo(MenuChildren::class, 'menu_children_id', 'menu_children_id');
    }

    public function privilege()
    {
        return $this->belongsTo(Privilege::class, 'privilege_id', 'privilege_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'menu_id');
    }
}

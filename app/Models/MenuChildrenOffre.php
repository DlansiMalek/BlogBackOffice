<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuChildrenOffre extends Model
{
    protected $table = 'Menu_Children_Offre';
    protected $primaryKey = 'menu_children_offre_id';
    protected $fillable = ['menu_children_id', 'offre_id', 'menu_id'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;

    public function menu_children()
    {
        return $this->belongsTo(MenuChildren::class, 'menu_children_id', 'menu_children_id');
    }

    public function offre()
    {
        return $this->belongsTo(Offre::class, 'offre_id', 'offre_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'menu_id');
    }
}

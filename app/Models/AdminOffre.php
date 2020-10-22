<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminOffre extends Model
{
    protected $table = 'Admin_Offre';
    protected $primaryKey = 'admin_offre_id';
    protected $fillable = ['admin_id', 'offre_id'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;

    public function admin()
    {
        return $this->belongsTo('App\Models\Admin', 'admin_id', 'admin_id');
    }

    public function offre()
    {
        return $this->belongsTo('App\Models\Offre', 'offre_id', 'offre_id');
    }
}

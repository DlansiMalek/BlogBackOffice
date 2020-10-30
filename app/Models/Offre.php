<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offre extends Model
{
    protected $table = 'Offre';
    protected $primaryKey = 'offre_id';
    protected $fillable = ['nom', 'value', 'start_date', 'end_date', 'status', 'type_id', 'admin_id'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at','deleted_at'];


    public function type() {
        return $this->hasOne('App\Models\Type', 'type_id', 'type_id');
    }

    public function admin() {
        return $this->hasOne('App\Models\Admin', 'admin_id', 'admin_id');
    }

}

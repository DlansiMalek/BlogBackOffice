<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offre extends Model
{
    protected $table = 'Offre';
    protected $primaryKey = 'offre_id';
    protected $fillable = ['prix_unitaire', 'type_commission_id', 'type_offre_id',
        'start_date', 'end_date', 'admin_id'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at','deleted_at'];

    public function type_offre() {
        return $this->hasOne('App\Models\TypeOffre', 'type_offre_id', 'type_offre_id');
    }

    public function type_commission() {
        return $this->hasOne('App\Models\TypeCommission', 'type_commission_id', 'type_commission_id');
    }

    public function admin() {
        return $this->hasOne('App\Models\Admin', 'admin_id', 'admin_id');
    }

}

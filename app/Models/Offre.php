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
        return $this->hasOne('App\Models\OffreType', 'offre_type_id', 'offre_type_id');
    }

    public function admin() {
        return $this->hasOne('App\Models\Admin', 'admin_id', 'admin_id');
    }

    public function payment_admin() {
        return $this->belongsTo('App\Models\PaymentAdmin', 'offre_id', 'offre_id');
    }

}

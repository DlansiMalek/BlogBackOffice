<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackAdmin extends Model
{
    protected $table = 'Pack_Admin';
    protected $primaryKey = 'pack_id';
    protected $fillable = ['name','type','capacity','price','nbr_days','nbr_events'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;

    public function modules()
    {
        return $this->belongsToMany('App\Models\Module', 'packadmin_module', 'pack_id','module_id');
    }
    public function PackPayments(){
        return $this->hasMany('App\Models\PaymentAdmin','pack_id','pack_id');
    }
    public function PackHistories(){
        return $this->hasMany('App\Models\HistoryPack','pack_id','pack_id');
    }
}

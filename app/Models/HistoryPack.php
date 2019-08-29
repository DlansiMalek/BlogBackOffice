<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class HistoryPack extends Model
{
    protected $table = 'History_Pack';
    protected $primaryKey = 'history_id';
    protected $fillable = ['status','start_date','end_date','nbr_events','pack_admin_id', 'admin_id'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];
    public function pack()
    {
        return $this->hasOne('App\Models\PackAdmin', 'pack_admin_id','pack_admin_id');
    }
}

<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class HistoryPack extends Model
{
    protected $table = 'History_pack';
    protected $primaryKey = 'history_id';
    protected $fillable = ['status','pack_admin_id', 'admin_id'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];
}

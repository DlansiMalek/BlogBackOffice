<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllowedOnlineAccess extends Model
{
    protected $table = 'Allowed_Online_Access';
    protected $primaryKey = 'allowed_online_access_id';
    protected $fillable = ['congress_id', 'privilege_id'];
    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhiteList extends Model
{
    protected $table = 'White_List';
    protected $primaryKey = 'white_list_id';
    protected $fillable = ['email', 'first_name', 'last_name', 'mobile', 'congress_id'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];


}

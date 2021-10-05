<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandContentConfig extends Model
{
    public $timestamps = true;
    protected $table = 'Stand_Content_Config';
    protected $primaryKey = 'stand_content_config_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['key','label', 'size' ,'default_file', 'default_url', 'accept_file', 'stand_type_id'];

    function stand_content_file()
    {
        return $this->belongsToMany(Stand::class,'Stand_Content_File','stand_content_config_id','stand_id');
    }

    function stand_type() {
        return $this->hasOne(StandType::class,'stand_type_id','stand_type_id');
    }
}

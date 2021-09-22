<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandContentFile extends Model
{
    public $timestamps = true;
    protected $table = 'Stand_Content_File';
    protected $primaryKey = 'stand_content_file_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['file','url', 'stand_id' ,'stand_content_config_id'];

    function stand_content_config()
    {
        return $this->belongsToMany(StandContentConfig::class,'stand_content_config_id','stand_content_config_id');
    }

}

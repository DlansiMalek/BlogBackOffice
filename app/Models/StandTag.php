<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandTag extends Model
{
    protected $table = 'Stand_Tag';
    protected $primaryKey = 'stand_tag_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['stag_id', 'stand_id'];

}

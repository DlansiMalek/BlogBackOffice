<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationType extends Model
{
    public $timestamps = true;
    protected $table = 'Communication_Type';
    protected $primaryKey = 'communication_type_id';
    protected $fillable = ['label'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

}

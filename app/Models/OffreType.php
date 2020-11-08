<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OffreType extends Model
{
    protected $table = 'Offre_Type';
    protected $primaryKey = 'offre_type_id';
    protected $fillable = ['name'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

}

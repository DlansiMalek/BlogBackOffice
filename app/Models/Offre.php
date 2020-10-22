<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offre extends Model
{
    protected $table = 'Offre';
    protected $primaryKey = 'offre_id';
    protected $fillable = ['name', 'prix', 'type_commission_id', 'start_dat', 'end_date'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at','deleted_at'];

}

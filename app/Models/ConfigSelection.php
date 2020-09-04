<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigSelection extends Model
{
    protected $table = 'Config_Selection';
    protected $primaryKey = 'config_selection_id';
    protected $fillable = ['num_evaluators','selection_type','congress_id','start_data','end_date'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;
}

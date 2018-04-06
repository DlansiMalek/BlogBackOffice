<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 29/03/2018
 * Time: 11:00
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Session_Stage extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'session_stage_id';
    protected $table = 'Session_Stage';
    public $timestamps = true;
    protected $fillable = ['name',
        'date_choice_open', 'date_choice_close', 'date_service_open', 'date_service_close',
        'capacity', 'niveau_id'];
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function niveau()
    {
        return $this->hasOne('App\Models\Niveau', 'niveau_id', 'niveau_id');
    }

    public function groupes()
    {
        return $this->hasMany('App\Models\Groupe', 'session_stage_id', 'session_stage_id');
    }
}

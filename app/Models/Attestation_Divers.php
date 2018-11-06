<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attestation_Divers extends Model
{
    public $timestamps = true;
    protected $table = 'Attestation_Divers';
    protected $primaryKey = 'attestation_divers_id';
    protected $fillable = ['attestation_generator_id', 'attestation_type_id', 'congress_id'];
    protected $dates = ['created_at', 'updated_at'];


    public function attestations()
    {
        return $this->hasMany('App\Models\Attestation_Divers', 'attestation_type_id', 'attestation_type_id');
    }

    public function type()
    {
        return $this->belongsTo('App\Models\Attestation_Type', 'attestation_type_id', 'attestation_type_id');
    }


}
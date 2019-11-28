<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttestationDivers extends Model
{
    public $timestamps = true;
    protected $table = 'Attestation_Divers';
    protected $primaryKey = 'attestation_divers_id';
    protected $fillable = ['attestation_generator_id', 'attestation_type_id', 'congress_id'];
    protected $dates = ['created_at', 'updated_at','deleted_at'];


    public function type()
    {
        return $this->belongsTo('App\Models\AttestationType', 'attestation_type_id', 'attestation_type_id');
    }


}
<?php
/**
 * Created by PhpStorm.
 * User: Moez
 * Date: 15/07/2020
 * Time: 2:18 PM
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class AttestationSubmission extends Model
{
    protected $table = 'Attestation_Submission';
    protected $primaryKey = 'attestation_submission_id';
    protected $fillable = ['attestation_generator_id_blank','enable', 'attestation_generator_id','congress_id','communication_type_id'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at','deleted_at'];



    public function communicationType() {
        return  $this->belongsTo('App\Models\CommunicationType','communication_type_id');
    }
    public function attestation_param() {
        return $this->hasMany('App\Models\AttestationParams', 'generator_id','attestation_generator_id');
    }
    public function attestation_blanc_param() {
        return $this->hasMany('App\Models\AttestationParams', 'generator_id','attestation_generator_id_blank');
    }
}
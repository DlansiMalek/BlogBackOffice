<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttestationType extends Model
{
    public $timestamps = true;
    protected $table = 'Attestation_Type';
    protected $primaryKey = 'attestation_type_id';
    protected $fillable = ['label'];
    protected $dates = ['created_at', 'updated_at'];


    public function attestations()
    {
        return $this->hasMany('App\Models\AttestationDivers', 'attestation_type_id', 'attestation_type_id');
    }


}
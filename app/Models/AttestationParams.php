<?php
/**
 * Created by PhpStorm.
 * User: Moez
 * Date: 15/07/2020
 * Time: 2:19 PM
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AttestationParams extends Model
{
    protected $table = 'Attestation_Params';
    protected $primaryKey = 'attestation_param_id';
    protected $fillable = ['key', 'generator_id'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at','deleted_at'];


}
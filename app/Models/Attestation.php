<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 12/09/2018
 * Time: 21:12
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Attestation extends Model
{
    protected $table = 'Attestation';
    protected $primaryKey = 'attestation_id';
    protected $fillable = ['attestation_generator_id_blank', 'attestation_generator_id', 'congress_id'];


    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at','deleted_at'];
}
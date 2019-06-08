<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 12/09/2018
 * Time: 21:12
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Attestation_Access extends Model
{
    protected $table = 'Attestation_Access';
    protected $primaryKey = 'attestation_access_id';
    protected $fillable = ['attestation_generator_id', 'access_id'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];
}
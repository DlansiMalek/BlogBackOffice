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

class Enseignant extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'enseignant_id';
    protected $table = 'Enseignant';
    public $timestamps = true;
    protected $fillable = ['CIN', 'nom', 'prenom', 'email', 'qr_code', 'service_id'];
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

}

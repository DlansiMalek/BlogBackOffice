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

class Etudiant extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'etudiant_id';
    protected $table = 'Etudiant';
    public $timestamps = true;
    protected $fillable = ['CIN', 'nom', 'prenom', 'carte_Etudiant', 'qr_code', 'email', 'niveau_id'];
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

}

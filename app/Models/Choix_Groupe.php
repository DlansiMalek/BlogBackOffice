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

class Choix_Groupe extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'choix_groupe_id';
    protected $table = 'Choix_Groupe';
    public $timestamps = true;
    protected $fillable = ['choice', 'real_affect', 'etudiant_id', 'groupe_id'];
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

}

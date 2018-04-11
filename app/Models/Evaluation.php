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

class Evaluation extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'evaluation_id';
    protected $table = 'Evaluation';
    public $timestamps = true;
    protected $fillable = ['note', 'note', 's_periode_id', 'etudiant_id', 'enseignant_id'];
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

}

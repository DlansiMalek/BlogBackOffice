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

class S_Groupe extends Model
{
    use SoftDeletes;
    protected $primaryKey = 's_groupe_id';
    protected $table = 'S_Groupe';
    public $timestamps = true;
    protected $fillable = ['label'];
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function services()
    {
        return $this->hasMany('App\Models\SGroupe_Service', 's_groupe_id', 's_groupe_id');
    }

    public function speriodes()
    {
        return $this->hasMany('App\Models\S_Periode', 's_groupe_id', 's_groupe_id');
    }
}

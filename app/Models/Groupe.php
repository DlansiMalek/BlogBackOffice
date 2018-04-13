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

class Groupe extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'groupe_id';
    protected $table = 'Groupe';
    public $timestamps = true;
    protected $fillable = ['label'];
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function sgroupes()
    {
        return $this->hasMany('App\Models\S_Groupe', 'groupe_id', 'groupe_id');
    }
}

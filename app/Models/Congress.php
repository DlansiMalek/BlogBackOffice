<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:16
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Congress extends Model
{
    protected $table = 'Congress';
    protected $primaryKey = 'id_Congress';
    protected $fillable = ['name', 'date'];

    public $timestamps = false;

    public function admin()
    {
        return $this->belongsToMany('App\Models\Admin', 'Congress_Admin', 'id_Congress', 'id_Admin');
    }
}
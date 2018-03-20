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
    protected $primaryKey = 'congress_id';
    protected $fillable = ['name', 'date'];

    protected $dates = ['created_at', 'updated_at'];
    public $timestamps = true;

    public function admin()
    {
        return $this->belongsToMany('App\Models\Admin', 'Congress_Admin', 'id_Congress', 'id_Admin');
    }
}
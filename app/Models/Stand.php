<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stand extends Model
{
    public $timestamps = true;
    protected $table = 'Stand';
    protected $primaryKey = 'stand_id';
    protected $fillable = ['name', 'congress_id', 'organization_id', 'url_streaming'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

}

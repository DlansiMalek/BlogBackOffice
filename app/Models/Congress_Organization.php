<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Congress_Organization extends Model
{
    protected $table = 'Congress_Organization';
    protected $primaryKey = 'congress_organization_id';
    protected $fillable = ['congress_id','organization_id','montant'];


    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];


}
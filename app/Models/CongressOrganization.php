<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:16
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CongressOrganization extends Model
{
    protected $table = 'Congress_Organization';
    protected $primaryKey = 'congress_organization_id';
    protected $fillable = ['congress_id', 'organization_id', 'montant', 'banner', 'resource_id', 'is_sponsor'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;

}
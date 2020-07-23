<?php
/**
 * Created by PhpStorm.
 * User: Moez
 * Date: 15/07/2020
 * Time: 2:19 PM
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class CommunicationType extends Model
{
    protected $table = 'Communication_Type';
    protected $primaryKey = 'communication_type_id';
    protected $fillable = ['label', 'abrv'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at','deleted_at'];
}
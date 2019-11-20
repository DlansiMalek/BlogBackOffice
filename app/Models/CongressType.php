<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 12/09/2018
 * Time: 21:12
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CongressType extends Model
{
    public $timestamps = true;
    protected $table = 'Congress_Type';
    protected $primaryKey = 'congress_type_id';
    protected $fillable = ['label'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
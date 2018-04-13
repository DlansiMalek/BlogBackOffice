<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:16
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'Country';
    protected $primaryKey = 'Code';
    protected $fillable = ['Name', 'nationality'];

    public $timestamps = false;

    public $incrementing = false;
}
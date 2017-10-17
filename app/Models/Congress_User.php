<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Congress_User extends Model
{
    protected $table = 'Congress_User';
    protected $primaryKey = 'id_Congress_User';
    protected $fillable = ['id_User', 'id_Congress', 'isPresent', 'isPaid', 'Mode_exercice',
        'pack',
        'reservation',
        'atelier',
        'Mode_payement',
        'prix_pack',
        'prix_reservation',
        'prix_total'];

    public $timestamps = true;

}
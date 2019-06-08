<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public $timestamps = true;
    protected $table = 'Payment';
    protected $primaryKey = 'payment_id';
    protected $fillable = ['isPaid','path','reference','authorization','free','price',
        'payment_type_id','user_id','congress_id','admin_id'];

    public function type(){
        return $this->hasOne('App\Models\Payment_Type','payment_type_id','payment_type_id');
    }

    public function user(){
        return $this->hasOne('App\Models\User','user_id','user_id');
    }

    public function admin(){
        return $this->hasOne('App\Models\Admin','admin_id','admin_id');
    }

    public function congress(){
        return $this->hasOne('App\Models\Congress','congress_id','congress_id');
    }

}
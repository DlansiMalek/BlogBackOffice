<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PaymentAdmin extends Model
{
    protected $table = 'Payment_Admin';
    protected $primaryKey = 'payment_id';
    protected $fillable = ['isPaid','reference','authorization','price','path', 'admin_id', 'payment_type_id', 'offre_id'];
    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];

    public function admin(){
        return $this->hasOne('App\Models\Admin','admin_id','admin_id');
    }

    public function payment_type()
    {
        return $this->hasOne('App\Models\PaymentType', 'payment_type_id', 'payment_type_id');
    }

    public function offre()
    {
        return $this->hasOne('App\Models\Offre', 'offre_id', 'offre_id');
    }
}

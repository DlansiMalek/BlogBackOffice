<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PaymentAdmin extends Model
{
    protected $table = 'Payment_admin';
    protected $primaryKey = 'payment_id';
    protected $fillable = ['isPaid','reference','authorization','price','path','pack_id', 'admin_id'];
    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];

    public function admin(){
        return $this->hasOne('App\Models\Admin','admin_id','admin_id');
    }
}

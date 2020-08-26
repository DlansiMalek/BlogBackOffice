<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomSMS extends Model
{

    protected $table = 'Custom_SMS';
    protected $primaryKey='custom_sms_id';
    protected $fillable=['admin_id','title','content'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public $timestamps=true;
}

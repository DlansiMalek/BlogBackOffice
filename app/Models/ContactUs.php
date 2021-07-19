<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    public $timestamps = true;
    protected $table = 'Contact_us';
    protected $primaryKey = 'contact_id';
    protected $fillable = ['email','user_name','subject','message'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

}

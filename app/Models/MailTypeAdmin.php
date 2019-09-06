<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MailTypeAdmin extends Model
{
    public $timestamps = true;
    protected $table = 'Mail_Type_Admin';
    protected $primaryKey = 'mail_type_id';
    protected $fillable = ['name', 'display_name'];

    protected $dates = ['created_at', 'updated_at','deleted_at'];

    public function mails()
    {
        return $this->hasMany(MailAdmin::class, 'mail_type_id', 'mail_type_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailAdmin extends Model
{
    //
    public $timestamps = true;
    protected $table = 'Mail_Admin';
    protected $primaryKey = 'mail_admin_id';
    protected $fillable = ['object','template','mail_type_admin_id'];
    protected $dates = ['created_at', 'updated_at','deleted_at'];

    public function type(){
        return $this->hasOne("App\Models\MailTypeAdmin","mail_type_admin_id","mail_type_admin_id");
    }
}

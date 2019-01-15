<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mail extends Model
{
    public $timestamps = true;
    protected $table = 'Mail';
    protected $primaryKey = 'mail_id';
    protected $fillable = ['congress_id','object','template','mail_type_id'];
    protected $dates = ['created_at', 'updated_at','deleted_at'];

    public function type(){
        return $this->hasOne("App\Models\Mail_Type","mail_type_id","mail_type_id");
    }
}
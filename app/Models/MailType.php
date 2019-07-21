<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailType extends Model
{
    public $timestamps = true;
    protected $table = 'Mail_Type';
    protected $primaryKey = 'mail_type_id';
    protected $fillable = ['name', 'display_name'];

    public function mails()
    {
        return $this->hasMany(Mail::class, 'mail_type_id', 'mail_type_id');
    }

}
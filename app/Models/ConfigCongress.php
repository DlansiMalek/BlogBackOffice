<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:16
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigCongress extends Model
{
    protected $table = 'Config_Congress';
    protected $primaryKey = 'config_congress_id';
    protected $fillable = [
        'logo',
        'banner',
        'free',
        'has_payment',
        'program_link',
        'feedback_start',
        'voting_token',
        'prise_charge_option',
        'auto_presence',
        'link_sondage',
        'nb_ob_access',
        'access_system',
        'congress_id',
        'status',
        'is_code_shown',
        'is_notif_register_mail',
        'is_notif_sms_confirm',
        'currency_code',
        'token_sms',
        'lydia_token',
        'lydia_api',
        'is_submission_enabled'
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;
}

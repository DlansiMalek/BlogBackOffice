<?php


namespace App\Services;

use App\Models\Mail;
use App\Models\MailAdmin;
use App\Models\MailType;
use App\Models\MailTypeAdmin;
use App\Models\UserMail;
use App\Models\UserMailAdmin;

class MailServices
{

    public function getAllMailTypes($congressId = null, $type)
    {
        return MailType::where('type','=',$type)
        ->with(['mails' => function ($query) use ($congressId) {
            if ($congressId)
                $query->where('congress_id', '=', $congressId);
        }])
            ->get();
    }

    public function getAllMailTypesAdmin()
    {
        return MailTypeAdmin::with(['mails'])
            ->get();
    }

    public function getMailTypeById($mailTypeId)
    {
        return MailType::find($mailTypeId);
    }

    public function getMailByTypeAndCongress($mailTypeId, $congressId)
    {
        return Mail::where('mail_type_id', '=', $mailTypeId)
            ->where('congress_id', '=', $congressId)
            ->first();
    }

    public function saveMail($congress_id, $mailTypeId, $object, $template)
    {

        $mail = new Mail();

        $mail->congress_id = $congress_id;
        $mail->object = $object;
        $mail->template = $template;
        $mail->mail_type_id = $mailTypeId;
        $mail->save();
        return $mail;
    }

    public function updateMail($mail, $object, $template)
    {
        $mail->object = $object;
        $mail->template = $template;

        $mail->update();

        return $mail;
    }

    public function getMailById($mailId)
    {
        return Mail::with(['type'])
            ->where('mail_id', '=', $mailId)
            ->first();
    }

    public function getMailByUserIdAndMailId($mailId, $userId)
    {
        return UserMail::where('user_id', '=', $userId)
            ->where('mail_id', '=', $mailId)
            ->first();
    }

    public function addingMailUser($mailId, $userId)
    {
        $mailUser = new UserMail();
        $mailUser->user_id = $userId;
        $mailUser->mail_id = $mailId;
        $mailUser->save();

        return $mailUser;
    }

    public function addingUserMailAdmin($mailAdminId, $userId)
    {
        $userMailAdmin = new UserMailAdmin();
        $userMailAdmin->user_id = $userId;
        $userMailAdmin->mail_admin_id = $mailAdminId;
        $userMailAdmin->save();

        return $userMailAdmin;
    }

    public function getMailAdminById($mailId)
    {
        return MailAdmin::with(['type'])
            ->where('mail_admin_id', '=', $mailId)
            ->first();
    }

    public function getMailTypeAdmin($name)
    {
        return MailTypeAdmin::where('name', '=', $name)->first();
    }

    public function getMailAdmin($mailTypeAdminId)
    {
        return MailAdmin::where('mail_type_admin_id', '=', $mailTypeAdminId)->first();
    }

    public function getMailTypeAdminById($mailTypeAdminId)
    {
        return MailTypeAdmin::find($mailTypeAdminId);
    }

    public function getMailAdminByMailTypeAdminId($mailTypeAdminId)
    {
        return MailAdmin::where('mail_type_admin_id', '=', $mailTypeAdminId)
            ->with(['type'])
            ->first();
    }

    public function getMailTypeAdminByMailTypeAdminId($mailTypeAdminId)
    {
        return MailTypeAdmin::where('mail_type_admin_id', '=', $mailTypeAdminId)
            ->first();
    }

    public function saveMailAdmin($mailTypeAdminId, $object, $template)
    {

        $mail = new MailAdmin();

        $mail->object = $object;
        $mail->template = $template;
        $mail->mail_type_admin_id = $mailTypeAdminId;
        $mail->save();
        return $mail;
    }

    public function updateMailAdmin($mail,$objet,$template,$mail_type_admin_id)
    {
        if (!$mail) {
            return null;
        }
        $mail->object = $objet;
        $mail->template = $template;
        $mail->mail_type_admin_id =$mail_type_admin_id;
        $mail->update();
        return $mail;
    }

    public function addMailAdmin($request, $mail)
    {
        $mail->mail_id = $request->input('mail_id');
        $mail->object = $request->input('object');
        $mail->template = $request->input('template');
        $mail->mail_type_id = $request->input('mail_type_id');
        $mail->save();
        return $mail;
    }

}

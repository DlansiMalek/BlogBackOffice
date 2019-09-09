<?php


namespace App\Services;


use App\MailAdmin;
use App\MailTypeAdmin;
use App\Models\Mail;
use App\Models\MailType;
use App\Models\UserMail;

class MailServices
{

    public function getAllMailTypes($congressId = null)
    {
        return MailType::
        with(['mails' => function ($query) use ($congressId) {
            if ($congressId)
                $query->where('congress_id', '=', $congressId);
        }])
            ->get();
    }

    public function getAllMailTypesAdmin()
    {
        return MailTypeAdmin::
        with(['mails'])
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
            ->
            first();
    }

    public function addingMailUser($mailId, $userId)
    {
        $mailUser = new UserMail();
        $mailUser->user_id = $userId;
        $mailUser->mail_id = $mailId;
        $mailUser->save();

        return $mailUser;
    }

    public function getMailAdminById($mailId)
    {
        return MailAdmin::with(['type'])
            ->where('mail_id', '=', $mailId)
            ->
            first();
    }

    public function updateMailAdmin($request, $mail)
    {
        if (!$mail) {
            return null;
        }
        $mail->mail_id = $request->input('mail_id');
        $mail->object = $request->input('object');
        $mail->template = $request->input('template');
        $mail->mail_type_id = $request->input('mail_type_id');
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
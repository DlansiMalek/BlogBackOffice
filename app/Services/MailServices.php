<?php


namespace App\Services;

use App\Models\Mail;
use App\Models\MailAdmin;
use App\Models\MailType;
use App\Models\MailTypeAdmin;
use App\Models\Offre;
use App\Models\UserMail;
use App\Models\UserMailAdmin;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Exception;


/**
 * @param \Exception $exception
 * @return void
 * @property \GuzzleHttp\Client client
 */
class MailServices
{
    protected $maxRequest = 0;

    public function getAllMailTypes($congressId , $type)
    {
        return MailType::where('type', '=', $type)
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

    public function getMailByUserIdAndMailId($mailId, $userId, $submissionId = null)
    {
        return UserMail::where('user_id', '=', $userId)
            ->where('mail_id', '=', $mailId)
            ->where(function ($query) use ($submissionId) {
                if ($submissionId != null) {
                    $query->where('submission_id', '=', $submissionId);
                }
            })
            ->first();
    }

    public function addingMailUser($mailId, $userId, $submissionId = null, $meetingId = null)
    {
        $mailUser = new UserMail();
        $mailUser->user_id = $userId;
        $mailUser->mail_id = $mailId;
        if ($submissionId) {
            $mailUser->submission_id = $submissionId;
        }
        if ($meetingId) {
            $mailUser->meeting_id = $meetingId;
        }
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

    public function updateMailAdmin($mail, $objet, $template, $mail_type_admin_id)
    {
        if (!$mail) {
            return null;
        }
        $mail->object = $objet;
        $mail->template = $template;
        $mail->mail_type_admin_id = $mail_type_admin_id;
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


    public function sendMail($view, $user, $congress, $objectMail, $fileAttached, $userMail = null, $toSendEmail = null, $fileName = null )
    {
        //TODO detect email sended user
        $email = $toSendEmail ? $toSendEmail : $user->email;
        $pathToFile = storage_path() . '/app/' . $fileName;
        $offre = null;

        if ($congress != null && $congress->username_mail)
            config(['mail.from.name', $congress->username_mail]);

        if ($congress != null) {
            $offre = $this->getOffreByCongressId($congress->congress_id);
        }
        // waiting sending mail
        if ($userMail) {
            $userMail->status = 2;
            $userMail->update();
        }
        if ($offre != null && $offre->is_mail_pro == 1) {
            $this->sendMailPro($view, $congress, $objectMail, $fileAttached, $email, $pathToFile, $userMail, $fileName);
        } else {
            $this->sendMailBasic($view, $congress, $objectMail, $fileAttached, $email, $pathToFile, $userMail, $fileName);
        }

    }

    public function sendMailPro($view, $congress, $objectMail, $fileAttached, $email, $pathToFile, $userMail, $fileName)
    {
        /*while ($this->maxRequest <= 3) {
                    try {
                        $this->sendMailUsingSendPulse($view, $user, $congress, $objectMail, $fileAttached, $email, $pathToFile);
                        return 1;
                    } catch (Exception $e) {
                        $this->maxRequest++;
                        if ($e->getCode() == 401)
                        {
                            $token_mail = $this->getKey($congress->config);
                            $this->sendMailUsingSendPulse($view, $user, $congress, $objectMail, $fileAttached, $email, $pathToFile, $token_mail);
                        }
                        Log::info($e->getMessage());
                        return $e->getMessage();
                    }
                }*/
        $response = $this->sendMailUsingSendInBlue($view, $congress, $objectMail, $fileAttached, $email, $pathToFile, $fileName);
        if ($response == 201) {
            if ($userMail) {
                $userMail->status = 1;
                $userMail->update();
            }
            if ($fileAttached) {
                $path = '/app/' . $fileName;
                Storage::delete($path);
            }
            return 1;
        } else {
            if ($userMail) {
                $userMail->status = -1;
                $userMail->update();
            }
            if ($fileAttached) {
                $path = '/app/' . $fileName;
                Storage::delete($path);
            }
            return 1;
        }
    }

    public function sendMailBasic($view, $congress, $objectMail, $fileAttached, $email, $pathToFile, $userMail, $fileName)
    {
        try {
            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($email, $congress, $pathToFile, $fileAttached, $objectMail, $view) {
                $fromMailName = $congress != null && $congress->config && $congress->config->from_mail ? $congress->config->from_mail : env('MAIL_FROM_NAME', 'Eventizer');
                if ($congress != null && $congress->config && $congress->config->replyto_mail) {
                    $message->replyTo($congress->config->replyto_mail);
                }

                $message->from(env('MAIL_USERNAME', 'contact@eventizer.io'), $fromMailName);
                $message->subject($objectMail);
                $message->setBody($view, 'text/html');
                if ($fileAttached)
                    $message->attach($pathToFile);
                $message->to($email)->subject($objectMail);
            });
        } catch (\Exception $exception) {
            if ($userMail) {
                $userMail->status = -1;
                $userMail->update();
            }
            if ($fileAttached) {
                $path = '/app/' . $fileName;
                Storage::delete($path);
            }
            return 1;
        }
        if ($userMail) {
            $userMail->status = 1;
            $userMail->update();
        }
        if ($fileAttached) {
            $path = '/app/' . $fileName;
            Storage::delete($path);
        }
        return 1;
    }

    public function getOffreByCongressId($congress_id)
    {
        return Offre::where('status', '=', 1)
            ->join('Admin_Congress', function ($join) use ($congress_id) {
                $join->on('Admin_Congress.admin_id', '=', 'Offre.admin_id')
                    ->where('congress_id', '=', $congress_id)
                    ->where('privilege_id', '=', config('privilege.Admin'));
            })->first();
    }


    public function sendMailUsingSendInBlue($view, $congress, $objectMail, $fileAttached, $email, $pathToFile, $fileName)
    {
        $html = $view->render();
        if ($fileAttached) {
            $img = file_get_contents($pathToFile);
            $content = base64_encode($img);
        }
        $fromMailName = $congress != null && $congress->config && $congress->config->from_mail ? $congress->config->from_mail : env('MAIL_FROM_NAME', 'Eventizer');
        $replyTo = $congress != null && $congress->config != null && $congress->config->replyto_mail!= null ? $congress->config->replyto_mail : env('MAIL_USERNAME', 'contact@eventizer.io');
        /* 
            TODO removing  
            $logMail = env('MAIL_LOG', 'logs@eventizer.io');
        */
        $message = array(
            'sender' => array(
                'email'=> $replyTo,
                'name' => $fromMailName,
            ),
            'htmlContent' => $html,
            'subject' => $objectMail,
            'replyTo' => array(
                'email' => $replyTo
            ),
            'to' => array(
                array(
                    'email' => $email,
                )
            ),
            /*'bcc' => array(
                array('email' => $logMail)
            ),*/
            'tags' => array(strval($congress->congress_id))
        );
        if ($fileAttached) {
            $file = array('attachment' => array(
                array(
                    // 'url' =>  $pathToFile,
                    'content' => $content,
                    'name' => $fileName
                    // try this for test: https://i1.wp.com/africanelephantjournal.com/wp-content/uploads/2019/04/3ac2e367385a4a378fb5cf7fc58d8ebc.jpg?resize=800%2C500&ssl=1
                )
            )
            );
            $message = array_merge($message, $file);
        }
        $this->client = new Client([
            'base_uri' => UrlUtils::getUrlSendInBlue(),
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'api-key' => env('API_KEY_SEND_BLUE')
            ],
            'http_errors' => false
        ]);
        $httpBody = json_encode($message);
        $res = $this->client->post('', [
            'body' => $httpBody
        ]);
        return json_decode($res->getStatusCode(), true);
    }

    /*    public function sendMailUsingSendPulse ($view, $user, $congress, $objectMail, $fileAttached, $email, $pathToFile, $token_mail = null)
        {
            $token = $token_mail ? $token_mail : ($congress ? $congress->config['token_mail_pro'] : '');
            if (!$token) {
                $token = 'erreur_token'; // car en cas où il n'ya pas de token le serveur envoie le status 400 et on passe jamais à l'obtention du token
            }
            $fromMailName = $congress != null && $congress->config && $congress->config->from_mail ? $congress->config->from_mail : env('MAIL_FROM_NAME', 'Eventizer');
            $name = $user ? $user->first_name .' ' . $user->last_name : '';
            $message = array(
                'html' => $view,
                // 'text' => 'Hello!',
                'subject' => $objectMail,
                'from' => array(
                    'email' =>  env('MAIL_USERNAME', 'contact@eventizer.io'),
                    'name' => $fromMailName,
                ),
                'to' => array(
                    array(
                        'name' => $name,
                        'email' => $email,
                    ),
                ),
            );
            if ($fileAttached)
            {
               $file =array( 'attachments' => array(
                'file.txt' => file_get_contents($pathToFile),
                   )
               );
                $message = array_merge($message, $file);
            }

            if(isset($message['html'])){
                $message['html'] = base64_encode($message['html']);
            }
            $data = array(
                'email' => serialize($message),
            );

            $this->client = new Client([
                'base_uri' => UrlUtils::getUrlSendPulse(),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' .  $token
                ]
            ]);

            $res = $this->client->post('/smtp/emails', [
                'form_params' => $data
            ]);

            return json_decode($res->getBody(), true);
        }*/

    /*    public function getKey($config)
        {
            $this->client = new Client([
                'base_uri' => UrlUtils::getUrlSendPulse(),
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'http_errors' => false
            ]);
            $res = $this->client->post('/oauth/access_token', [
                'form_params' => [
                    'client_id' => env('API_USER_ID_SENPULSE'),
                    'client_secret' => env('API_SECRET_SENPULSE'),
                    'grant_type' => 'client_credentials'
                ]
            ]);
            if ($config) {
                $config->token_mail_pro = json_decode($res->getBody(), true)['access_token'];
                $config->update();
            }

            return json_decode($res->getBody(), true)['access_token'];
        }*/

}

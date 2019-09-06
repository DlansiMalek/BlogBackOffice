<?php

namespace App\Http\Controllers;


use App\MailAdmin;
use App\Models\AttestationRequest;
use App\Models\User;
use App\Models\UserCongress;
use App\Services\AccessServices;
use App\Services\AdminServices;
use App\Services\BadgeServices;
use App\Services\CongressServices;
use App\Services\MailServices;
use App\Services\OrganizationServices;
use App\Services\PackServices;
use App\Services\SharedServices;
use App\Services\UserServices;
use App\Services\Utils;
use http\Env\Response;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MailController extends Controller
{

    protected $mailService;

    function __construct(MailServices $mailService)
    {
        $this->mailService = $mailService;
    }


    public function getAllMailTypes($congressId)
    {
        return $this->mailService->getAllMailTypes($congressId);
    }
    public function getAllMailTypesAdmin()
    {
        return $this->mailService->getAllMailTypesAdmin();
    }
    public function getMailTypeById($mailTypeId)
    {
        return $this->mailService->getMailTypeById($mailTypeId);
    }

    public function getById($mail_id)
    {
        return $this->mailService->getMailById($mail_id);
    }
    public function getMailAdminById($mailId)
    {
        return $this->mailService->getMailAdminById($mailId);
    }


    public function getByMailTypeAndCongress($mailTypeId, $congressId)
    {
        return $this->mailService->getMailByTypeAndCongress($mailTypeId, $congressId);
    }


    public function saveMail(Request $request, $congress_id, $mailTypeId)
    {
        if (!$request->has(['object', 'template']))
            return response()->json(['resposne' => 'bad request', 'required fields' => ['object', 'template']], 400);


        $mail = null;
        if ($request->has('mailId')) {
            $mail = $this->mailService->getMailById($request->input('mailId'));
        }

        if ($mail || ($mailTypeId != 4 && $mail = $this->mailService->getMailByTypeAndCongress($mailTypeId, $congress_id))) {
            $mail = $this->mailService->updateMail($mail, $request->input('object'), $request->input('template'));
            //return response()->json(['response' => 'mail exist']);
        } else {
            $mail = $this->mailService->saveMail($congress_id, $mailTypeId, $request->input('object'), $request->input('template'));
        }
        return $mail;
    }


    public function uploadMailImage(Request $request)
    {
        $file = $request->file('image');
        $chemin = config('media.mail-images');
        $path = $file->store('mail-images' . $chemin);
//        return $path."+++".substr($path,12);
        return response()->json(['link' => $this->baseUrl . "congress/file/" . substr($path, 12)]);
    }

    public function updateMailAdmin(Request $request, $mail_id)
    {
        $mail = $this->mailService->getMailAdminById($mail_id);
        if (!$mail) {
            return response()->json(['response' => 'Mail not found'], 404);
        }
        return response()->json($this->mailService->updateMailAdmin($request, $mail), 202);
    }

    public function storeMailAdmin(Request $request)
    {
        if (!$request->has(['object', 'template']))
        return response()->json(['resposne' => 'bad request', 'required fields' => ['object', 'template']], 400);

        $mail = new MailAdmin();
            $this->mailService->AddMailAdmin($request, $mail);
            return response()->json(['response' => 'Mail added with success'], 202);
        }
}

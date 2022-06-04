<?php

namespace App\Http\Controllers;


use App\Services\AdminServices;
use App\Services\ContactUsServices;
use App\Services\MailServices;
use Illuminate\Http\Request;
class ContactUsController extends Controller
{
    protected $adminServices;
    protected $mailServices;
    protected $contactUsServices;
    public function __construct(AdminServices $adminServices,
        MailServices $mailServices,
        ContactUsServices $contactUsServices
    ) {
        $this->adminServices = $adminServices;
        $this->mailServices = $mailServices;
        $this->contactUsServices = $contactUsServices;
    }
    public function addContactUs(Request $request)
    {
        $contact =  $this->contactUsServices->addContactUs($request);
        if (!$mailTypeAdmin = $this->mailServices->getMailTypeAdmin('contact_us')) {
            return response()->json(['message' => 'Mail type not found'], 400);
        }
        $mailAdmin = $this->mailServices->getMailAdmin($mailTypeAdmin->mail_type_admin_id);
        if (!$mailAdmin) {
            return response()->json(['message' => 'Mail not found'], 400);
        }
        $this->mailServices->sendMAil($this->adminServices->renderMail($mailAdmin->template, null, null, null, null, null, $contact), null, null, $contact->subject, false, null, env('MAIL_USERNAME', 'contact@eventizer.io'));
        return response()->json(['message' => 'mail sent with success']);
    }
}

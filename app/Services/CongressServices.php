<?php

namespace App\Services;

use App\Models\Congress;
use App\Models\Custom_Mail;
use App\Models\Form_Input;
use App\Models\Form_Input_Value;
use App\Models\Organization;
use App\Models\Pack;
use App\Models\User;
use Chumper\Zipper\Facades\Zipper;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use JWTAuth;
use PDF;

/**
 * @property OrganizationServices $organizationServices
 */
class CongressServices
{


    public function __construct(OrganizationServices $organizationServices)
    {
        $this->organizationServices = $organizationServices;
    }

    public function getCongressById($id_Congress)
    {
        return Congress::with(["badges", "users.grade", "attestation", "accesss.participants", "accesss.attestation", "accesss","packs.accesses","form_inputs.type","form_inputs.values", "custom_mails"])
            ->where("congress_id", "=", $id_Congress)
            ->first();
    }

    function retrieveCongressFromToken()
    {
        Config::set('jwt.user', 'App\Models\Congress');
        Config::set('jwt.identifier', 'id_Congress');
        try {
            return JWTAuth::parseToken()->toUser();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return null;
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return null;
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return null;
        }
    }

    public function addCongress($name, $date,$email,$object_mail_inscription, $object_mail_payement,$object_mail_attestation, $admin_id)
    {
        $congress = new Congress();
        $congress->name = $name;
        $congress->date = $date;
        $congress->admin_id = $admin_id;
        $congress->username_mail = $email;
        $congress->object_mail_inscription = $object_mail_inscription;
        $congress->object_mail_payement = $object_mail_payement;
        $congress->object_mail_attestation = $object_mail_attestation;
        $congress->save();
        return $congress;
    }

    public function getCongressAllAccess($adminId)
    {
        return Congress::with(["accesss","packs.accesses"])
            ->where("admin_id", "=", $adminId)
            ->get();
    }

    public function getBadgesByUsers($badgeName, $users)
    {

        $users = $users->toArray();
        $file = new Filesystem();
        $path = public_path() . "/" . $badgeName;

        if (!$file->exists($path)) {
            $file->makeDirectory($path);
        }
        $qrCodePath = "/QrCode";
        if (!$file->exists(public_path() . $qrCodePath)) {
            $file->makeDirectory(public_path() . $qrCodePath);
        }

        File::cleanDirectory($path);
        for ($i = 0; $i < sizeof($users) / 4; $i++) {
            $tempUsers = array_slice($users, $i * 4, 4);
            $j = 1;
            $pdfFileName = '';
            foreach ($tempUsers as $tempUser) {
                Utils::generateQRcode($tempUser['qr_code'], $qrCodePath . '/qr_code_' . $j . '.png');
                $pdfFileName .= '_' . $tempUser['user_id'];
                $j++;
            }
            $data = [
                'users' => json_decode(json_encode($tempUsers), false)];
            $pdf = PDF::loadView('pdf.' . $badgeName, $data);
            $pdf->save($path . '/badges' . $pdfFileName . '.pdf');
        }
        $files = glob($path . '/*');
        $file->deleteDirectory(public_path() . $qrCodePath);
        Zipper::make($path . '/badges.zip')->add($files)->close();
        return response()->download($path . '/badges.zip')->deleteFileAfterSend(true);

    }

    public function editCongress($congress, $adminId, $request)
    {
        $congress->name = $request->input("name");
        $congress->date = $request->input("date");
        $congress->admin_id = $adminId;
        $congress->username_mail = $request->input("username_mail");
        $congress->object_mail_inscription = $request->input('object_mail_inscription');
        $congress->object_mail_payement = $request->input('object_mail_payement');
        $congress->object_mail_attestation = $request->input('object_mail_attestation');

        $congress->update();

        return $congress;
    }

    public function getUsersByStatus($congressId, int $status)
    {
        return User::with(['grade'])
            ->where('isPresent', '=', $status)
            ->where('congress_id', '=', $congressId)
            ->get();
    }

    public function getLabsByCongress($congressId)
    {
        return Organization::with(['users' => function ($q) use ($congressId) {
            $q->where('User.congress_id', '=', $congressId);
        }])->whereHas('users', function ($q) use ($congressId) {
            $q->where('User.congress_id', '=', $congressId);
        })->get();
    }

    public function getOrganizationInvoiceByCongress($labId, $congress)
    {
        $lab = $this->organizationServices->getOrganizationById($labId);
        $totalPrice = 0;
        $packs = Pack::whereCongressId($congress->congress_id)->with(['participants' => function ($q) use ($labId) {
            $q->where('User.organization_id', '=', $labId);
        }])->get();
        foreach ($packs as $pack) {
            $packPrice = $pack->price;
            $pack->price = 0;
            foreach ($pack->participants as $participant) {
                $pack->price += $packPrice;
            }
            $totalPrice += $pack->price;
        }
        $today = date('d-m-Y');
        $data = [
            'packs' => $packs,
            'congress' => $congress,
            'today' => $today,
            'lab' => $lab,
            'totalPrice' => $totalPrice,
            'displayTaxes' => false
        ];
        $pdf = PDF::loadView('pdf.invoice.invoice', $data);
        return $pdf->download($lab->name . '_facture_' . $today . '.pdf');
    }

    public function getBadgeByPrivilegeId($congress, $privilege_id)
    {
        for ($i = 0; $i < sizeof($congress->badges); $i++) {
            if ($congress->badges[$i]->privilege_id == $privilege_id) {
                return $congress->badges[$i]->badge_id_generator;
            }
        }
        return null;
    }

    public function uploadLogo($congress, Request $request)
    {
        $file = $request->file('file_data');
        $chemin = config('media.congress-logo');
        $path = $file->store($chemin);

        $congress->logo = $path;
        $congress->update();

        return $congress;
    }

    public function addFormInputs($inputs, $congress_id){
        $old = Form_Input::where("congress_id" ,'=',$congress_id );
        foreach($old as $input){
            Form_Input_Value::where('form_input_id','=',$input->form_input_id)->delete();
        }
        $old->delete();
        foreach ($inputs as $inputRequest){
            $input = new Form_Input();
            $input->form_input_type_id = $inputRequest["type"]["form_input_type_id"];
            $input->congress_id = $congress_id;
            $input->label = $inputRequest["label"];
            $input->save();
            if($inputRequest["type"]["name"] == "checklist"||$inputRequest["type"]["name"] == "multiselect"||$inputRequest["type"]["name"] == "select"||$inputRequest["type"]["name"] == "radio"){
                foreach ($inputRequest["values"] as $valueRequest){
                    $value = new Form_Input_Value();
                    $value->value = $valueRequest['value'];
                    $value->form_input_id= $input->form_input_id;
                    $value->save();
                }
            }
        }
    }

    public function saveCustomMail(\App\Models\Custom_Mail $mail)
    {
        $mail->save();
    }

    public function getEmailById($id)
    {
        return Custom_Mail::find($id);
    }

}

<?php

namespace App\Services;

use App\Models\Congress;
use App\Models\Form_Input;
use App\Models\Form_Input_Value;
use App\Models\Mail;
use App\Models\Mail_Type;
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
        return Congress::with(["badges", "users.privilege", "users.responses.values", "attestation", "accesss.participants", "accesss.attestation", "accesss","packs.accesses","form_inputs.type","form_inputs.values", "mails.type"])
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

    public function addCongress($name, $date,$email,$has_paiement, $admin_id)
    {
        $congress = new Congress();
        $congress->name = $name;
        $congress->date = $date;
        $congress->admin_id = $admin_id;
        $congress->username_mail = $email;
        $congress->has_paiement = $has_paiement;
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
        $congress->has_paiement = $request->input('has_paiement');
        $congress->update();

        return $congress;
    }

    public function getUsersByStatus($congressId, int $status)
    {
        return User::where('isPresent', '=', $status)
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

    public function getEmailById($id)
    {
        return Mail::find($id);
    }

    function renderMail($template,$congress, $participant,$link){
        $accesses = "";
        if (sizeof($participant->accesss)>0){
            $accesses = "<p>Votre pré-inscription à (l'/aux) atelier(s) :</p><ul>";
            foreach ($participant->accesss as $access){
                $accesses = $accesses
                    ."<li>".$access->name
                    ."<span class=\"bold\"> qui se déroulera le "
                    .\App\Services\Utils::convertDateFrench($access->theoric_start_data)
                    ." de "
                    .\App\Services\Utils::getTimeFromDateTime($access->theoric_start_data)
                    ." à "
                    .\App\Services\Utils::getTimeFromDateTime($access->theoric_end_data)
                    ." </span></li>";
            }
            $accesses = $accesses."</ul>";
        }
        $template = str_replace('{{$congress-&gt;name}}','{{$congress->name}}',$template);
        $template = str_replace('{{$congress-&gt;date}}','{{$congress->date}}',$template);
        $template = str_replace('{{$congress-&gt;price}}','{{$congress->price}}',$template);
        $template = str_replace('{{$participant-&gt;first_name}}','{{$participant->first_name}}',$template);
        $template = str_replace('{{$participant-&gt;last_name}}','{{$participant->last_name}}',$template);
        $template = str_replace('{{$participant-&gt;gender}}','{{$participant->gender}}',$template);
        $template = str_replace('{{$participant-&gt;accesses}}',$accesses,$template);
        $template = str_replace('{{%24link}}',"{{$link}}",$template);
        if ($participant!=null)
            $participant->gender = $participant->gender==1?'Mr.':'Mme';
        return view(['template'=>'<html>'.$template.'</html>'],['congress'=>$congress, 'participant'=>$participant,'link'=>$link]);
    }

    public function getMailType($name){
        return Mail_Type::where("name","=",$name)->first();
    }

    public function getMail($congressId, $mail_type_id)
    {
        return Mail::where("congress_id",'=',$congressId)->where('mail_type_id','=',$mail_type_id)->first();
    }

    public function getMailById($id)
    {
        return Mail::find($id);
    }

}

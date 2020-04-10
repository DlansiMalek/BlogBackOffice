<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;


use App\Models\Admin;
use App\Models\AdminCongress;
use App\Models\Congress;
use App\Models\HistoryPack;
use App\Models\ThemeAdmin;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JWTAuth;

class AdminServices
{

    public function getAdminByLogin($login)
    {
        return Admin::whereEmail($login)
            ->with(["congresses", "congresses.form_inputs.values"])
            ->first();

    }

    public function retrieveAdminFromToken()
    {
        try {
            return auth()->user();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            $refreshed = JWTAuth::refresh(JWTAuth::getToken());
            $user = JWTAuth::setToken($refreshed)->toUser();
            header('Authorization: Bearer ' . $refreshed);
            return $user;
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return null;
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return null;
        }
    }

    public function getAdminById($admin_id)
    {
        return Admin::where("admin_id", "=", $admin_id)
            ->with(['admin_congresses.congress.badges', 'admin_congresses.congress.config', 'admin_congresses.congress.form_inputs.values', 'admin_congresses.privilege'])
            ->first();
    }

    public function getAdminByMail($admin_mail)
    {
        return Admin::where("email", "=", $admin_mail)
            ->first();
    }

    public function getClients()
    {
        return Admin::where("privilege_id", "=", 1)
            ->with(['AdminHistories.pack'])
            ->get();
    }

    public function getClienthistoriesbyId($id)
    {
        return Admin::where("privilege_id", "=", 1)->where('admin_id', '=', $id)
            ->with(['AdminHistories.pack'])
            ->get();
    }

    public function gethistorybyId($id)
    {
        return HistoryPack::where("history_id", "=", $id)
            ->first();
    }

    public function getClientcongressesbyId($id)
    {
        return Admin::where("privilege_id", "=", 1)->where('admin_id', '=', $id)
            ->with(['congresses'])
            ->get();
    }

    public function AddAdmin(Request $request, $admin)
    {
        $admin->name = $request->input('name');
        $admin->mobile = $request->input('mobile');
        $admin->email = $request->input('email');
        $admin->privilege_id = 1;
        $admin->passwordDecrypt = app('App\Http\Controllers\SharedController')->randomPassword();
        $admin->password = app('App\Http\Controllers\SharedController')->encrypt($admin->passwordDecrypt);
        $admin->save();
        return $admin;
    }
    
    public function getAllEvaluators(){

      return Admin::where("privilege_id","=",11)->get();

    }

    public function getEvaluatorsByCongress($congressId,$privilegeId){

            return Admin::whereHas('admin_congresses',function($query) use ($congressId,$privilegeId)
                {
                    $query->where('congress_id','=',$congressId);
                    $query->where('privilege_id','=',$privilegeId);
                })
                ->withCount(['submission'=> function($query) use ($congressId){
                    $query->where('congress_id','=',$congressId);
                }])
                ->orderBy('submission_count','asc')
                ->get();


           

    }

    public function getEvaluatorsByTheme($themeId,$congressId,$privilegeId){

        return Admin::whereHas('themeAdmin',function($query) use ($privilegeId,$themeId)
            {
                $query->where('privilege_id','=',$privilegeId);
                $query->where('theme_id','=',$themeId);
            
            })
            ->withCount(['submission'=> function($query) use ($congressId){
                $query->where('congress_id','=',$congressId);   
            }])
            ->orderBy('submission_count','asc')
            ->get();


       

    }
  
    public function addHistory($history, $admin, $pack)
    {
        $history->admin_id = $admin->admin_id;
        $history->pack_admin_id = $pack->pack_admin_id;
        $history->status = 0;
        $history->nbr_events = $pack->nbr_events;
        $history->save();
    }

    public function addPayment($payment, $admin, $pack)
    {
        $payment->admin_id = $admin->admin_id;
        $payment->pack_admin_id = $pack->pack_admin_id;
        $payment->isPaid = false;
        $payment->reference = "";
        $payment->authorization = "";
        $payment->path = "";
        $payment->save();
    }

    public function addValidatedHistory($history, $admin, $pack, $lasthistory)
    {
        $history->admin_id = $admin->admin_id;
        $history->pack_admin_id = $pack->pack_admin_id;
        $history->status = 1;
        if ($pack->type == 'Event') {
            $history->nbr_events = $pack->nbr_events; //$lasthistory->nbr_events - 1;
        } else {
            $history->nbr_events = $pack->nbr_events;
        }
        if ($pack->type == 'Duree') {
            $date = new DateTime();
            $history->start_date = $date->format('Y-m-d H:i:s');
            $date->add(new DateInterval('P' . $pack->nbr_days . 'D'));
            $history->end_date = $date->format('Y-m-d H:i:s');
        } else {
            $date = new DateTime();
            $history->start_date = $date->format('Y-m-d H:i:s');
            $history->end_date = $date->format('Y-m-d H:i:s');
        }
        $history->save();
    }

    public function getAdminCongresses(Admin $admin)
    {
        return Congress::whereHas('admin_congresses', function ($query) use ($admin) {
            $query->where('admin_id', '=', $admin->admin_id);
        })->get();
    }

    public function getListPersonelsByAdmin($congress_id)
    {
        return Admin::whereHas('admin_congresses', function ($query) use ($congress_id) {
            $query->where('congress_id', '=', $congress_id);
        })
            ->with(['admin_congresses' => function ($query) use ($congress_id) {
                $query->where('congress_id', '=', $congress_id);
            }, 'admin_congresses.privilege'])
            ->get();
    }

    public function getPersonelsByIdAndCongressId($congress_id, $admin_id)
    {
        return Admin::where('admin_id', '=', $admin_id)
//        ->whereHas('admin_congresses', function ($query) use ($congress_id) {
//            $query->where('congress_id', '=', $congress_id);
//        })
            ->with(['admin_congresses' => function ($query) use ($congress_id, $admin_id) {
                $query->where('congress_id', '=', $congress_id)
                    ->where('admin_id', '=', $admin_id)
                    ->first();
            }])->with(['themeAdmin'=>function ($query) use ($admin_id){
                $query->where('admin_id','=',$admin_id);  
                //on a besoin du themeAdmin pour effectuer l'edit
            }])
            ->first();
    }

    public function addResponsibleCongress($responsibleIds, $congress_id)
    {
        foreach ($responsibleIds as $responsibleId) {
            $congressAdmin = new AdminCongress();
            $congressAdmin->admin_id = $responsibleId;
            $congressAdmin->congress_id = $congress_id;
            $congressAdmin->save();
        }
    }

    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function generateNewPassword(Admin $admin)
    {
        $newPassword = $this->generateRandomString(20);
        Admin::where('admin_id', '=', $admin->admin_id)
            ->update(['passwordDecrypt' => $newPassword,
                'password' => bcrypt($newPassword)]);
        return $newPassword;
    }

    public function sendForgetPasswordEmail(Admin $admin)
    {


    }

    public function addPersonnel($admin,$privilegeId)
    {
        $personnel = new Admin();
        $personnel->name = $admin["name"];
        $personnel->email = $admin["email"];
        $personnel->mobile = $admin["mobile"];

        $password = Str::random(8);
        $personnel->passwordDecrypt = $password;
        $personnel->password = bcrypt($password);
        $personnel->privilege_id=$privilegeId;
        $personnel->save();

        return $personnel;
    }

    public function editPersonnel($admin,$privilegeId)
    {
        return Admin::where("admin_id", "=", $admin['admin_id'])
            ->update(['name' => $admin["name"],
                'email' => $admin["email"],
                'mobile' => $admin["mobile"],
                'privilege_id'=>$privilegeId]);

    }

    public function deleteAdminById($admin)
    {
        $admin->delete();
    }

    public function affectThemesToAdmin($themesIds,$admin_id){

       foreach($themesIds as $themeId){
           $themeAdmin=new ThemeAdmin();
           $themeAdmin->theme_id=$themeId;
           $themeAdmin->admin_id=$admin_id;
           $themeAdmin->save();
       }     
    }

    public function modifyAdminThemes($admin_id,$themesIds){
        
        $themeAdmin=ThemeAdmin::where('admin_id','=',$admin_id)->get();
        $loopLength=sizeof($themeAdmin)<sizeof($themesIds) ? sizeof($themeAdmin) :  sizeof($themesIds);

        //1)update 
        for($i=0;$i<$loopLength;$i++){
            $themeAdmin[$i]['theme_id']=$themesIds[$i];
            $themeAdmin[$i]->update();
        }
        
        //2)soit creér des nouveau themeAdmin soit en supprimer selon la taille des tableaux
        
        //le cas ou themeAdmin > themeIds donc on va supprimer les autres themes de cet admin

        if (sizeof($themeAdmin)>sizeof($themesIds)){

            for ($i=sizeof($themesIds);$i<sizeof($themeAdmin);$i++){
                
                   $themeAdmin[$i]->delete();
                
            }
        }
        //le cas ou themeadmin < themeIds donc on va affecter des themes à cet admin
        else {
            for ($i=sizeof($themeAdmin);$i<sizeof($themesIds);$i++){
                
                    $themeAdmin=new ThemeAdmin();
                    $themeAdmin->theme_id=$themesIds[$i];
                    $themeAdmin->admin_id=$admin_id;
                    $themeAdmin->save();
                
            }
        }
        return $themeAdmin;
    }

    public function getAdminByQrCode($QrCode)
    {
        //TODO Fixing with the new Design
        $admin = Admin::where("passwordDecrypt", "=", $QrCode)
            ->first();
        $admin->admin = $admin->privilege_id == 1;
        return $admin;
    }

    public function getConnectedAdmin(Request $request)
    {
        if (!request()->user()) return null;
        if (!$admin = $this->getAdminById($request->user()['admin_id'])) return null;
        return $admin;
    }

    public function updateAdmin(Request $request, $updateAdmin)
    {
        if (!$updateAdmin) {
            return null;
        }
        $updateAdmin->name = $request->input('name');
        $updateAdmin->email = $request->input('email');
        $updateAdmin->mobile = $request->input('mobile');
        $updateAdmin->name = $request->input('name');

        $updateAdmin->update();
        return $updateAdmin;
    }

    public function addPackToAdmin(Request $request, HistoryPack $history)
    {
        $history->admin_id = $request->admin_id;
        $history->pack_admin_id = $request->pack_admin_id;
        $history->status = $request->status;
        $history->start_date = $request->start_date;
        $history->end_date = $request->end_date;
        $history->nbr_events = $request->nbr_events;
        if ($request->nbr_events) {
            $date = new DateTime();
            $history->start_date = $date->format('Y-m-d H:i:s');
            $history->end_date = $date->format('Y-m-d H:i:s');
        }
        $history->save();
    }

    public function checkHasPrivilegeByCongress($admin_id, $congress_id)
    {
        return AdminCongress::where('admin_id', '=', $admin_id)
            ->where('congress_id', '=', $congress_id)
            ->first();
    }

    public function addAdminCongress($adminId, $congressId, $privilegeId)
    {
        $adminCongress = new AdminCongress();
        $adminCongress->admin_id = $adminId;
        $adminCongress->congress_id = $congressId;
        $adminCongress->privilege_id = $privilegeId;
        $adminCongress->save();
    }

    public function sendMail($view, $congress, $objectMail, $admin, $fileAttached, $customEmail = null)
    {

        $email = $admin ? $admin->email : $customEmail;
        $pathToFile = storage_path() . "/app/badge.png";

        try {
            Mail::send([], [], function ($message) use ($email, $congress, $pathToFile, $fileAttached, $objectMail, $view) {
                $message->from(env('MAIL_USERNAME', 'contact@eventizer.io'), $congress->name);
                $message->subject($objectMail);
                $message->setBody($view, 'text/html');
                if ($fileAttached)
                    $message->attach($pathToFile);
                $message->to($email)->subject($objectMail);
            });
        } catch (\Exception $exception) {
            Storage::delete('app/badge.png');
            return 1;
        }
        Storage::delete('app/badge.png');
        return 1;
    }
}

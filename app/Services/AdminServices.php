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
use App\Models\AdminOffre;
use App\Models\Congress;
use App\Models\Evaluation_Inscription;
use App\Models\MailTypeAdmin;
use App\Models\MailAdmin;
use App\Models\Offre;
use App\Models\PaymentAdmin;
use App\Models\SubmissionEvaluation;
use App\Models\ThemeAdmin;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JWTAuth;
use phpDocumentor\Reflection\Types\Null_;

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
            ->with(['admin_congresses.congress.config', 'admin_congresses.congress.form_inputs.values', 'admin_congresses.privilege'])
            ->first();
    }

    public function getAdminWithCurrentCongressFirst($admin_id, $congress_id)
    {
        return Admin::where("admin_id", "=", $admin_id)
            ->with(['admin_congresses' => function ($query) use ($congress_id) {
                $query->orderByRaw("FIELD(congress_id,$congress_id) DESC")
                    ->orderBy('congress_id', 'asc');
            },
                'admin_congresses.congress.config' ,
                'admin_congresses.congress.form_inputs.values', 'admin_congresses.privilege'])
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
            ->with(['offres' => function ($query) {
                $query->where('status', '=', 1)->with('payment_admin');
            }])
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

    public function getAllEvaluators()
    {

        return Admin::where("privilege_id", "=", 11)->get();
    }

    public function affectUsersToEvaluator($users, $numEvalutors, $admin_id, $congress_id)
    {
        $loopLength = sizeof($users) < $numEvalutors ? sizeof($users) : $numEvalutors;
        for ($i = 0; $i < sizeof($users); $i++) {
            $this->addEvaluationInscription(
                $admin_id,
                $congress_id,
                $users[$i]->user_id
            );
        }
    }

    public function affectEvaluatorToSubmissions($submissions, $admin_id, $themeIds, $congress_id)
    {
        $evalutors = $this->getEvaluatorsByTheme($submissions[0]->theme_id, $congress_id, 11); //get by theme or all admin congress
        $max = sizeof($evalutors) > 0 ? $evalutors[sizeof($evalutors) - 1]['submission_count'] : 0;
        $count = 0;
        foreach ($submissions as $submission) {
            foreach ($themeIds as $themeId) {
                if (($submission->theme_id == $themeId) && ($count <= $max)) {
                    $congress = json_decode($submission['congress'], true);
                    if (sizeof($submission['submissions_evaluations']) < $congress['config_submission']['num_evaluators']) {
                        $this->addSubmissionEvaluation($admin_id, $submission->submission_id);
                    }
                    break;
                }
            }
        }

        return 1;
    }

    public function addSubmissionEvaluation($admin_id, $submission_id)
    {
        $submissionEvaluation = new SubmissionEvaluation();
        $submissionEvaluation->submission_id = $submission_id;
        $submissionEvaluation->admin_id = $admin_id;
        $submissionEvaluation->save();
        return $submissionEvaluation;
    }

    public function getEvaluatorsBySubmissionId($submission_id)
    {
        return SubmissionEvaluation::where('submission_id', '=', $submission_id)->get();
    }

    public function getEvaluatorsByCongress($congressId, $privilegeId, $relation)
    {

        return Admin::whereHas('admin_congresses', function ($query) use ($congressId, $privilegeId) {
            $query->where('congress_id', '=', $congressId);
            $query->where('privilege_id', '=', $privilegeId);

        })
            ->withCount([$relation => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }])
            ->orderBy($relation . '_count', 'asc')
            ->get();
    }

    public function getEvaluatorsByTheme($themeId, $congressId, $privilegeId)
    {

        return Admin::whereHas('themeAdmin', function ($query) use ($privilegeId, $themeId) {

            $query->where('theme_id', '=', $themeId);
        })
            ->whereHas('admin_congresses', function ($query) use ($congressId, $privilegeId) {
                $query->where('congress_id', '=', $congressId);
                $query->where('privilege_id', '=', $privilegeId);
            })
            ->withCount(['submission' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }])
            ->orderBy('submission_count', 'asc')
            ->get();
    }

    public function getEvaluationInscriptionByIdAndCongressId($evaluation_inscription_id, $congress_id)
    {
        return Evaluation_Inscription::where('evaluation_inscription_id', '=', $evaluation_inscription_id)
            ->where('congress_id', '=', $congress_id)
            ->first();
    }

    public function getEvaluatorsByThemeOrByCongress($themeId, $congressId, $privilegeId)
    {
        $admins = $this->getEvaluatorsByTheme($themeId, $congressId, $privilegeId);
        if (sizeof($admins) < 1) {
            $admins = $this->getEvaluatorsByCongress($congressId, $privilegeId, 'submission');
        }
        return $admins;
    }

    public function addHistory($history, $admin, $pack)
    {
        $history->admin_id = $admin->admin_id;
        $history->pack_admin_id = $pack->pack_admin_id;
        $history->status = 0;
        $history->nbr_events = $pack->nbr_events;
        $history->save();
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
            }])->with(['themeAdmin' => function ($query) use ($admin_id) {
                $query->where('admin_id', '=', $admin_id);
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
            ->update([
                'passwordDecrypt' => $newPassword,
                'password' => bcrypt($newPassword)
            ]);
        return $newPassword;
    }

    public function addPersonnel($admin, $password)
    {
        $personnel = new Admin();
        $personnel->name = $admin["name"];
        $personnel->email = $admin["email"];
        $personnel->mobile = $admin["mobile"];
        $personnel->passwordDecrypt = $password;
        $personnel->password = bcrypt($password);
        $personnel->save();

        return $personnel;
    }

    public function editPersonnel($admin)
    {
        return Admin::where("admin_id", "=", $admin['admin_id'])
            ->update([
                'name' => $admin["name"],
                'email' => $admin["email"],
                'mobile' => $admin["mobile"]
            ]);
    }

    public function deleteAdminById($admin)
    {
        $admin->delete();
    }

    public function affectThemesToAdmin($themesIds, $admin_id)
    {

        foreach ($themesIds as $themeId) {
            $themeAdmin = new ThemeAdmin();
            $themeAdmin->theme_id = $themeId;
            $themeAdmin->admin_id = $admin_id;
            $themeAdmin->save();
        }
    }

    public function modifyAdminThemes($themesAdmin, $admin_id, $themesIds)
    {

        $loopLength = sizeof($themesAdmin) < sizeof($themesIds) ? sizeof($themesAdmin) : sizeof($themesIds);

        //1)update 
        for ($i = 0; $i < $loopLength; $i++) {
            $themesAdmin[$i]['theme_id'] = $themesIds[$i];
            $themesAdmin[$i]->update();
        }

        //2)soit creér des nouveau themeAdmin soit en supprimer selon la taille des tableaux

        //le cas ou themeAdmin > themeIds donc on va supprimer les autres themes de cet admin

        if (sizeof($themesAdmin) > sizeof($themesIds)) {

            for ($i = sizeof($themesIds); $i < sizeof($themesAdmin); $i++) {

                $themesAdmin[$i]->delete();

            }
        } //le cas ou themeadmin < themeIds donc on va affecter des themes à cet admin
        else {
            for ($i = sizeof($themesAdmin); $i < sizeof($themesIds); $i++) {

                $themeAdmin = new ThemeAdmin();
                $themeAdmin->theme_id = $themesIds[$i];
                $themeAdmin->admin_id = $admin_id;
                $themeAdmin->save();

            }
        }
        return $themesAdmin;
    }

    public function getThemeAdmin($admin_id)
    {
        return ThemeAdmin::where('admin_id', '=', $admin_id)->get();
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
                $message->from(env('MAIL_USERNAME', 'contact@eventizer.io'), env('MAIL_FROM_NAME', 'Eventizer'));
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

    public function addClient($admin, $request)
    {
        if (!$admin)
            $admin = new Admin();

        $admin->name = $request->input("name");
        $admin->email = $request->input("email");
        $admin->mobile = $request->input("mobile");
        $admin->passwordDecrypt = $request->input("passwordDecrypt");
        $admin->password = bcrypt($admin->passwordDecrypt);
        if ($request->has("valid_date")) {
            $admin->valid_date = $request->input("valid_date");
        }
        $admin->privilege_id = 1;
        $admin->save();
        return $admin;

    }

    public function affectEvaluatorsToUser($evaluators, $numEvalutors, $congress_id, $user_id)
    {
        $loopLength = sizeof($evaluators) < $numEvalutors ? sizeof($evaluators) : $numEvalutors;
        for ($i = 0; $i < $loopLength; $i++) {
            $this->addEvaluationInscription(
                $evaluators[$i]->admin_id,
                $congress_id,
                $user_id
            );
        }
    }

    public function addEvaluationInscription($admin_id, $congress_id, $user_id)
    {

        $evaluation = new Evaluation_Inscription();
        $evaluation->admin_id = $admin_id;
        $evaluation->congress_id = $congress_id;
        $evaluation->user_id = $user_id;
        $evaluation->save();

    }

    public function renderMail($template, $admin = null, $user = null, $activationLink = null, $linkBackOffice = null, $paymentLink = null)
    {
        $template = str_replace('{{$admin-&gt;email}}', '{{$admin->email}}', $template);
        $template = str_replace('{{$admin-&gt;passwordDecrypt}}', '{{$admin->passwordDecrypt}}', $template);
        $template = str_replace('{{$admin-&gt;name}}', '{{$admin->name}}', $template);
        $template = str_replace('{{$user-&gt;first_name}}', '{{$user->first_name}}', $template);
        $template = str_replace('{{$user-&gt;last_name}}', '{{$user->last_name}}', $template);

        return view(['template' => '<html>' . $template . '</html>'], ['admin' => $admin, 'user' => $user, 'linkBackOffice' => $linkBackOffice, 'activationLink' => $activationLink, 'paymentLink' => $paymentLink]);
    }

    public function getClientById($admin_id)
    {
        return Admin::where('admin_id', '=', $admin_id)->where('privilege_id', '=', 1)
            ->with(['offres', 'adminPayment', 'offres.type'])
            ->first();
    }

    public function editClient($request, $admin)
    {
        if (!$admin) {
            return null;
        }
        $admin->name = $request->input('name');
        $admin->mobile = $request->input('mobile');
        $admin->valid_date = $request->input('valid_date');
        $admin->update();

        return $admin;
    }

    public function editAdminPayment($adminPayment, $isPaid)
    {
        $adminPayment->isPaid = $isPaid;
        $adminPayment->update();
        return $adminPayment;
    }

    public function getAdminPayment($admin_id, $offre_id)
    {
        return PaymentAdmin::where('admin_id', '=', $admin_id)
            ->where('offre_id', '=', $offre_id)
            ->first();
    }

    public function updatePaymentAdminPrice($adminPayment, $value)
    {
        $adminPayment->price += $value;
        $adminPayment->update();
        return $adminPayment;
    }

}

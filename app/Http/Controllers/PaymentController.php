<?php

namespace App\Http\Controllers;


use App\Services\CongressServices;
use App\Services\PaymentServices;
use App\Services\SharedServices;
use App\Services\UserServices;
use App\Services\Utils;
use Illuminate\Http\Request;

class PaymentController extends Controller
{

    protected $paymentServices;
    protected $userServices;
    protected $congressServices;
    protected $sharedServices;

    function __construct(PaymentServices $paymentServices,
                         UserServices $userServices,
                         CongressServices $congressServices,
                         SharedServices $sharedServices)
    {
        $this->paymentServices = $paymentServices;
        $this->userServices = $userServices;
        $this->congressServices = $congressServices;
        $this->sharedServices = $sharedServices;
    }

    function echecPayment()
    {
        return response()->json(['error' => 'echec payment']);
    }

    function successPayment()
    {
        return response()->json(['message' => 'success payment']);
    }

    function notification(Request $request)
    {

        $action = $request->input("Action");
        $ref = $request->input("Reference");
        $param = $request->input("Param");

        $user = $this->userServices->getUserByRef($ref);

        switch ($action) {
            case "DETAIL" :
                if (!$user) {
                    $price = -1;
                } else {
                    $price = $user->price;
                }
                return "Reference=" . $ref . "&Action=" . $action . "&Reponse=" . $price;
            case "ACCORD" :
                $user->isPaied = 1;
                $user->autorisation_num = $param;
                $user->update();

                $congress = $this->congressServices->getCongressById($user->congress_id);

                $badgeIdGenerator = $this->congressServices->getBadgeByPrivilegeId($congress,
                    $user->privilege_id);
                $fileAttached = false;
                if ($badgeIdGenerator != null) {
                    $this->sharedServices->saveBadgeInPublic($badgeIdGenerator,
                        ucfirst($user->first_name) . " " . strtoupper($user->last_name),
                        $user->qr_code);
                    $fileAttached = true;
                }

                $link = Utils::baseUrlWEB . "/#/user/" . $user->user_id . "/manage-account?token=" . $user->verification_code;
                if ($mailtype = $this->congressServices->getMailType('paiement')){
                    if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)){
                        $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user), $user, $congress, $mail->object, $fileAttached,
                            $link);
                    }
                }



                return "Reference=" . $ref . "&Action=" . $action . "&Reponse=OK";

            case "REFUS":
                $user->isPaied = 0;
                $user->update();

                return "Reference=" . $ref . "&Action=" . $action . "&Reponse=OK";

            case "ERREUR":
                $user->isPaied = 0;
                $user->update();

                return "Reference=" . $ref . "&Action=" . $action . "&Reponse=OK";

            case "ANNULATION":
                $user->isPaied = 0;
                $user->update();

                return "Reference=" . $ref . "&Action=" . $action . "&Reponse=OK";
        }

        return "";
    }

}
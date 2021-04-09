<?php

namespace App\Http\Controllers;


use App\Models\Payment;
use App\Services\CongressServices;
use App\Services\MailServices;
use App\Services\PaymentServices;
use App\Services\SharedServices;
use App\Services\SmsServices;
use App\Services\UserServices;
use App\Services\Utils;
use App\Services\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $smsServices;
    protected $paymentServices;
    protected $userServices;
    protected $congressServices;
    protected $sharedServices;
    protected $mailServices;

    function __construct(PaymentServices $paymentServices,
                         UserServices $userServices,
                         CongressServices $congressServices,
                         SharedServices $sharedServices,
                         SmsServices $smsServices,
                         MailServices $mailServices)
    {
        $this->smsServices = $smsServices;
        $this->paymentServices = $paymentServices;
        $this->userServices = $userServices;
        $this->congressServices = $congressServices;
        $this->sharedServices = $sharedServices;
        $this->mailServices = $mailServices;
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

        $userPayment = $this->paymentServices->getPaymentByReference($ref);

        if (!$userPayment) {
            return "";
        }

        $congress = $userPayment->congress;

        $user = $this->userServices->getUserByIdWithRelations($userPayment->user->user_id, ['accesses' => function ($query) use ($congress) {
            $query->where('congress_id', '=', $congress->congress_id);
        }]);

        switch ($action) {
            case "DETAIL" :
                if (!$user) {
                    $price = -1;
                } else {
                    $price = $userPayment->price;
                }
                return "Reference=" . $ref . "&Action=" . $action . "&Reponse=" . $price;
            case "ACCORD" :
                $userPayment->isPaid = 1;
                $userPayment->authorization = $param;
                $userPayment->update();
                $userCongress = $this->userServices->getUserCongress($congress->congress_id, $user->user_id);
                $badge = $this->congressServices->getBadgeByPrivilegeId($congress,
                    $userCongress->privilege_id);
                $badgeIdGenerator = $badge['badge_id_generator'];
                $fileAttached = false;
                $fileName = "badge.png";
                if ($badgeIdGenerator != null) {
                    $fileAttached = $this->sharedServices->saveBadgeInPublic($badge,
                        $user,
                        $user->qr_code,
                        $userCongress->privilege_id);

                }


                if ($mailtype = $this->congressServices->getMailType('paiement')) {
                    if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                        $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, $userPayment), $user, $congress, $mail->object, false,
                            $userMail);
                    }
                }
                if ($mailtype = $this->congressServices->getMailType('confirmation')) {
                    $linkFrontOffice = UrlUtils::getBaseUrlFrontOffice() . '/login';
                    if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                        $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, $userPayment, null, $linkFrontOffice), $user, $congress, $mail->object, $fileAttached, $userMail, null, $fileName);
                    }

                    $this->smsServices->sendSms($congress->congress_id, $user, $congress);
                }


                return "Reference=" . $ref . "&Action=" . $action . "&Reponse=OK";

            case "REFUS":
                $userPayment->isPaid = 0;
                $userPayment->update();

                return "Reference=" . $ref . "&Action=" . $action . "&Reponse=OK";

            case "ERREUR":
                $userPayment->isPaid = 0;
                $userPayment->update();

                return "Reference=" . $ref . "&Action=" . $action . "&Reponse=OK";

            case "ANNULATION":
                $userPayment->isPaid = 0;
                $userPayment->update();

                return "Reference=" . $ref . "&Action=" . $action . "&Reponse=OK";
        }

        return "";
    }


    public function getPaymentsPagination(Request $request)
    {
        if (!$user = $this->userServices->retrieveUserFromToken()) {
            return response()->json(['message' => 'user not found']);
        }
        $user_id = $user->user_id;
        $offset = $request->query('offset', 0);
        $perPage = $request->query('perPage', 6);
        $search = $request->query('search', '');
        $status = $request->query('status');
        $method = $request->query('method');
        $min = $request->query('min',null);
        $max = $request->query('max',null);
        return $this->paymentServices->getPaymentPagination($user_id, $offset, $perPage, $search, $status, $method, $min, $max);
    }
    public function getPaymentByID(Request $request, $paymentID){
        return $this->paymentServices->getPaymentByID($request, $paymentID);
    }
    public function getPaymentByUserAndCongressID(Request $request, $congressID, $userID){
        return $this->paymentServices->getPaymentByUserAndCongressID($request, $congressID, $userID);
    }





}

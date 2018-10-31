<?php

namespace App\Http\Controllers;


use App\Services\PaymentServices;
use App\Services\UserServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{

    protected $paymentServices;
    protected $userServices;

    function __construct(PaymentServices $paymentServices,
                         UserServices $userServices)
    {
        $this->paymentServices = $paymentServices;
        $this->userServices = $userServices;
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


        switch ($action) {
            case "DETAIL" :
                $user = $this->userServices->getUserByRef($ref);
                if (!$user) {
                    $price = -1;
                } else {
                    $price = $user->price;
                }
                return "Reference=" . $ref . "&Action=" . $action . "&Reponse=" . $price;
            case "ACCORD" :
                $user = $this->userServices->getUserByRef($ref);
                $user->isPaied = 1;
                $user->autorisation_num = $param;
                $user->update();

                return "Reference=" . $ref . "&Action=" . $action . "&Reponse=NO";

            case "REFUS":
                return "Reference=" . $ref . "&Action=" . $action . "&Reponse=OK";

            case "ERREUR":
                return "Reference=" . $ref . "&Action=" . $action . "&Reponse=OK";

            case "ANNULATION":
                return "Reference=" . $ref . "&Action=" . $action . "&Reponse=OK";
        }

        return "";
    }

}
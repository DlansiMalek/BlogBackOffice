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

        $action = $request->input("action");
        $ref = $request->input("reference");
        $param = $request->input("param");

        Log::info($action);
        Log::info($ref);
        Log::info($param);

        switch ($action) {
            case "DETAIL" :
                $user = $this->userServices->getUserByRef($ref);
                if (!$user) {
                    $price = -1;
                } else {
                    $price = $user->price;
                }
                Log::info("reference=" . $ref . "&action=" . $action . "&reponse=" . $price);
                return "reference=" . $ref . "&action=" . $action . "&reponse=" . $price;
            case "ACCORD" :
                $user = $this->userServices->getUserByRef($ref);
                $user->isPaied = 1;
                $user->autorisation_num = $param;
                $user->update();

                return "reference=" . $ref . "&action=" . $action . "&reponse=OK";

            case "REFUS":
                return "reference=" . $ref . "&action=" . $action . "&reponse=OK";

            case "ERREUR":
                return "reference=" . $ref . "&action=" . $action . "&reponse=OK";

            case "ANNULATION":
                return "reference=" . $ref . "&action=" . $action . "&reponse=OK";
        }

        return "";
    }

}
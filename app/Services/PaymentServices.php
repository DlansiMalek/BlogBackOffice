<?php
/**
 * Created by IntelliJ IDEA.
 * User: ABBES
 * Date: 30/10/2018
 * Time: 18:50
 */

namespace App\Services;


use App\Models\Payment;
use App\Models\PaymentType;

class PaymentServices
{

    public function affectPaymentToUser($user_id, $congress_id, $price, $free)
    {
        $payment = new Payment();

        $payment->user_id = $user_id;
        $payment->congress_id = $congress_id;
        $payment->free = $free;
        $payment->price = $price;
        $payment->save();

        return $payment;
    }

    public function getFreeUserByCongressId($congress_id)
    {
        return Payment::where('free', '=', true)
            ->where('congress_id', '=', $congress_id)
            ->count();
    }

    public function getAllPaymentTypes()
    {
        return PaymentType::all();
    }

    public function getPaymentByReference($ref)
    {
        return Payment::where('reference', '=', $ref)
            ->first();
    }
}
<?php
/**
 * Created by IntelliJ IDEA.
 * User: ABBES
 * Date: 30/10/2018
 * Time: 18:50
 */

namespace App\Services;


use App\Models\Congress;
use App\Models\Payment;
use App\Models\PaymentType;
use Illuminate\Support\Facades\Storage;

class PaymentServices
{
    public function getPaymentByID($request, $paymentID){
        $payment = Payment::where('payment_id','=',$paymentID)
            ->join('Congress','Congress.congress_id','=','Payment.congress_id')
            ->first();
        return $payment;
    }

    public function getPaymentByUserAndCongressID($request, $congressID, $userID){
        $payment = Payment::where([
            ['Payment.user_id','=', $userID],
            ['Payment.congress_id', '=', $congressID]
        ])
            ->join('Congress','Congress.congress_id','=','Payment.congress_id')
            ->first();
        return $payment;
    }
    public function affectPaymentToUser($user_id, $congress_id, $price, $free)
    {
        if ($price && $price > 0) {
            $payment = new Payment();

            $payment->user_id = $user_id;
            $payment->congress_id = $congress_id;
            $payment->free = $free;
            $payment->price = $price;
            $payment->save();

            return $payment;
        }
        return null;
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

    public function getPaymentPagination($user_id, $offset, $perPage, $search, $status, $method, $min, $max)
    {
        $congresses_id = Congress::where([
        ['name', 'LIKE', '%' . $search . '%']
            ])->get('congress_id');
        $all_payments = Payment::where([
            ['user_id', '=', $user_id],
            ['free', '=', '0'],
            ['Payment.price','>', '0'],
        ])
            ->join('Congress','Congress.congress_id','=','Payment.congress_id')
            ->orderBy('Payment.price', 'desc')
            ->offset($offset)->limit($perPage)
            ->whereIn('Payment.congress_id', $congresses_id)
            ->Where('isPaid', 'LIKE', $status)
            ->Where('Payment.price', '>', $min)
            ->Where('Payment.price', '<', $max)
            ->Where('payment_type_id', 'LIKE', $method)->get();
       $payment_renderer = $all_payments->map(function ($payment)  {
            return collect($payment->toArray())
                ->only(["payment_id", "isPaid", "reference",
                    "authorisation", "price", "free", "congress_id", "user_id", "payment_type_id","name","updated_at"])->all();
        });


        return $payment_renderer;
    }
}

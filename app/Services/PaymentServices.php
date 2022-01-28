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

    public function getPaymentByUserAndCongressID($congressID, $userID){
        return Payment::where([
            ['Payment.user_id','=', $userID],
            ['Payment.congress_id', '=', $congressID]
        ])
            ->with(['congress'])
            ->first();
    }
    public function affectPaymentToUser($user_id, $congress_id, $price, $free, $isPaid = 0)
    {
        $payment = new Payment();

        $payment->user_id = $user_id;
        $payment->congress_id = $congress_id;
        $payment->free = $free;
        $payment->price = $price;
        $payment->isPaid = $isPaid;
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
            ->with(['congress'])
            ->orderBy('Payment.price', 'desc')
            ->offset($offset)->limit($perPage)
            ->whereIn('Payment.congress_id', $congresses_id)
            ->Where('isPaid', 'LIKE', $status)
            ->when($min !== 'null', function ($query) use ($min) {
                $query->where('Payment.price', '>', $min);
            })
            ->when($max !== 'null', function ($query) use ($max) {
                $query->where('Payment.price', '<', $max);
            })
            ->when($method !== 'null', function ($query) use ($method) {
                $query->where('payment_type_id', '=', $method);
            })->get();


        return $all_payments;
}
    public function changeIsPaidStatus($user_id,$congress_id,$status)
    {
        return Payment::where('user_id', '=', $user_id)
        ->where('congress_id', '=', $congress_id)
        ->update(['isPaid' => $status]);
    }
    public function getAllPaymentsByCongressId($congress_id) {
        return Payment::where('congress_id', '=', $congress_id)
        ->get();

    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Metiers\CongressServices;
use App\Models\Congress;
use Illuminate\Http\Request;
use JWTAuth;

class LoginController extends Controller
{

    protected $congressServices;


    function __construct(CongressServices $congressServices)
    {
        $this->congressServices = $congressServices;
    }

    public function loginCongress(Request $request)
    {
        $credentials = $request->only('login', 'password');
        $congress = Congress::whereLogin($request->input("login"))->first();

        try {
            // verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid credentials'], 401);
            }
        } catch (\JWTException $e) {
            return response()->json(['error' => 'could not create token'], 500);
        }
        return response()->json(['congress' => $congress, 'token' => $token], 200);
    }
}

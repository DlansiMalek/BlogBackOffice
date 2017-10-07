<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Metiers\AdminServices;
use Illuminate\Http\Request;
use JWTAuth;

class LoginController extends Controller
{

    protected $adminServices;

    public function __construct(AdminServices $adminServices)
    {
        $this->adminServices = $adminServices;
    }

    public function loginAdmin(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $admin = $this->adminServices->getAdminByLogin($request->input("email"));

        try {
            // verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid credentials'], 401);
            }
        } catch (\JWTException $e) {
            return response()->json(['error' => 'could not create token'], 500);
        }
        return response()->json(['admin' => $admin, 'token' => $token], 200);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use App\Services\AdminServices;
use App\Services\PrivilegeServices;
use Illuminate\Http\Request;


class LoginController extends Controller
{

    protected $adminServices;
    protected $privilegeServices;

    public function __construct(AdminServices $adminServices, PrivilegeServices $privilegeServices)
    {
        $this->adminServices = $adminServices;
        $this->privilegeServices = $privilegeServices;
    }

    /**
     * @SWG\Post(
     *   path="/auth/login/admin",
     *   summary="Login Admin",
     *   operationId="loginAdmin",
     *   @SWG\Parameter(
     *     name="email",
     *     in="query",
     *     description="Email Admin.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="query",
     *     description="Password Admin.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function loginAdmin(Request $request)
    {
        $credentials = request(['email', 'password']);
        $admin = $this->adminServices->getAdminByLogin($request->input("email"));


        // verify the credentials and create a token for the user
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'invalid credentials'], 401);
        }

        return response()->json(['admin' => $admin, 'token' => $token], 200);
    }



    /**
     * @SWG\Post(
     *   path="/mobile/login",
     *   summary="Login",
     *   tags={"Mobile"},
     *   operationId="loginAdminMobile",
     *   @SWG\Parameter(
     *     name="QrCode",
     *     in="query",
     *     description="QR code",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function loginAdminMobile(Request $request)
    {
        $admin = $this->adminServices->getAdminByQrCode($request->input("QrCode"));
        $credentials = array(
            "email" => $admin->email,
            "password" => $admin->passwordDecrypt
        );

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'invalid credentials'], 401);
        }

        return response()->json(['admin' => $admin, 'token' => $token], 200);
    }
}

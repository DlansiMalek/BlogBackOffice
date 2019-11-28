<?php

namespace App\Http\Middleware;

use App\Models\AdminCongress;
use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class Organisateur
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Try {
            $user = JWTAuth::parseToken()->toUser();
            $adminCongresses = AdminCongress::where('admin_id', '=', $user->admin_id)
                ->whereIn('privilege_id', [1, 2])
                ->get();
            if (sizeof($adminCongresses) != 0) {
                return $next($request);
            } else
                return response()->json(['error' => 'Permission denied'], 403);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'Token Expired'], 403);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Token Invalid'], 403);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Permission denied'], 403);
        }
    }
}

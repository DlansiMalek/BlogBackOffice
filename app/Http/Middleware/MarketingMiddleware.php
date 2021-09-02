<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class MarketingMiddleware
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
            if (JWTAuth::parseToken()->toUser()->privilege_id == config('privilege.Super_Admin') || JWTAuth::parseToken()->toUser()->privilege_id == config('privilege.Marketing'))
                return $next($request);
            else
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

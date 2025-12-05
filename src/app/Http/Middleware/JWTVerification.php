<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }
        try {
            // Set the token first
            JWTAuth::setToken($token);
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            Log::error('Expired JWT Token: ' . $e->getMessage(), [
                'token' => substr($token, 0, 20) . '...', // Log partial token for debugging
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            Log::error('Invalid JWT Token: ' . $e->getMessage(), [
                'token' => substr($token, 0, 20) . '...', // Log partial token for debugging
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Token is invalid'], 401);
        } catch (\Exception $e) {
            Log::error('JWT Verification Error: ' . $e->getMessage(), [
                'token' => substr($token, 0, 20) . '...', // Log partial token for debugging
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Could not authenticate token'], 401);
        }
        return $next($request);
    }
}

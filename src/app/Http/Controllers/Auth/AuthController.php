<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        try {
            $validatedData = $request->validated();

            // Map username from request to the actual authentication field
            // Try to authenticate with username field
            $credentials = [
                'username' => $validatedData['username'],
                'password' => $validatedData['password']
            ];

            // Attempt authentication with username
            if (!$token = JWTAuth::attempt($credentials)) {
                // If username auth fails, try with email (in case username contains an email)
                $emailCredentials = [
                    'email' => $validatedData['username'],
                    'password' => $validatedData['password']
                ];

                if (!$token = JWTAuth::attempt($emailCredentials)) {
                    return response()->json(['error' => 'Invalid credentials'], 401);
                }
            }

            // Get the authenticated user - token is already properly created by JWTAuth::attempt()
            $user = JWTAuth::user();
        } catch (ValidationException $e) {
            Log::error(
                'Validation error during login',
                [
                    'errors' => $e->errors(),
                    "trace" => $e->getTraceAsString()
                ]
            );
            return response()->json(['errors' => $e->errors()], 422);
        } catch (JWTException $e) {
            Log::error(
                'JWT error during login',
                [
                    'message' => $e->getMessage(),
                    "trace" => $e->getTraceAsString()
                ]
            );
            return response()->json(['error' => 'Could not create token'], 500);
        } catch (\Throwable $e) {
            Log::error(
                'General error during login',
                [
                    'message' => $e->getMessage(),
                    "trace" => $e->getTraceAsString()
                ]
            );
            return response()->json(['error' => 'An error occurred during login'], 500);
        }

        return response()->json([
            'expired_at' => now()->addMinutes(config('jwt.ttl'))->format('Y-m-d H:i:s'),
            'token' => $token,
            'type' => 'Bearer'
        ], 200);
    }


    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'User logged out successfully'], 200);
        } catch (JWTException $e) {
            Log::error(
                'JWT error during logout',
                [
                    'message' => $e->getMessage(),
                    "trace" => $e->getTraceAsString()
                ]
            );
            return response()->json(['error' => 'Could not invalidate token'], 500);
        } catch (\Throwable $e) {
            Log::error(
                'Could not invalidate token during logout',
                [
                    'message' => $e->getMessage(),
                    "trace" => $e->getTraceAsString()
                ]
            );
            return response()->json(['error' => 'An error occurred during logout'], 500);
        }
    }

    public function me()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            return response()->json(['user' => $user], 200);
        } catch (JWTException $e) {
            Log::error(
                'JWT error during fetching user info',
                [
                    'message' => $e->getMessage(),
                    "trace" => $e->getTraceAsString()
                ]
            );
            return response()->json(['error' => 'Could not fetch user'], 500);
        }
    }
}

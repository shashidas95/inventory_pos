<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Helpers\JwtToken;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->validated();

            // print_r($credentials);
            // dd();
            $user = User::whereEmail($credentials['email'])->first();
            // Check if the user exists and the password is correct
            if (!$user || !Hash::check($credentials['password'], $user->password)) {
              
                return response()->json([
                    'status' => false,
                    'errors' => 'Invalid Credentials',
                ], 401); // Use HTTP 401 Unauthorized status for login failures.
            }
            $userData = [
                'name' => $user->name,
                'email' => $user->email,
                'id' => $user->id,
                'role' => $user->role,
                'image' => $user->profile->avatar_url
            ];
            $exp = 60 * 24;
            $token = JwtToken::createToken($userData, time() + $exp);
            if ($token['error']) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token creation failed'
                ], 500);
            }
            return response()->json([
                'status' => true,
                'message' => 'Login Success',
                'data' => new UserResource($user),
            ], 200)->cookie('token', $token['token'], $exp);
            # code...
        } catch (\Exception $e) {
            Log::critical($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }
}

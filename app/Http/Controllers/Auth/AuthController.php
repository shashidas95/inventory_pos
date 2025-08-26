<?php

namespace App\Http\Controllers\Auth;

use App\Models\Otp;
use App\Models\User;
use App\Models\Profile;
use App\Helpers\JwtToken;
use App\Mail\SendOtpMail;
use Illuminate\Support\Arr;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;

class AuthController extends Controller
{

    public function register(RegisterRequest $request): JsonResponse
    {

        $validated = $request->validated();
        $userData = Arr::only($validated, ['email', 'password', 'name', 'role']);
        $profileData = Arr::only($validated, ['phone', 'address']);

        try {
            $user = User::create($userData);
            $profileData['user_id'] = $user->id;


            if ($request->hasFile('image')) {
                $path  = $request->file('image')->store('avatars', 'public');
                $profileData['avatar'] = $path;
            }
            // print_r($profileData);
            // exit;
            Profile::create($profileData);

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            Log::critical($e->getMessage() . ' '  . $e->getFile() . ' ' . $e->getLine());
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->validated();

            // print_r($credentials);
            // dd();
            $user = User::whereEmail($credentials['email'])->first();
            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'status' => false,
                    'errors' => 'Invalid Credentials',
                ]);
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
                'user_data' => $userData,
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



    public function sendOtp(ForgotPasswordRequest $request)
    {
        try {
            $otp = mt_rand(100000, 999999);
            Otp::create([
                'email' => $request->email,
                'otp' => $otp,
            ]);
            Mail::to($request->email)->send(new SendOtpMail($otp));
            return response()->json([
                'status' => true,
                'message' => 'An otp is sent to your email',

            ]);
        } catch (\Exception $e) {
            Log::critical($e->getMessage() . ' '  . $e->getFile() . ' ' . $e->getLine());
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }
    public function verifyOtp(VerifyOtpRequest $request)
    {
        try {

            // Validation handled by the FormRequest, so we know the OTP is valid here
            Otp::where('email', $request->email)
                ->where('otp', $request->otp)
                ->update(['status' => true]);

            $exp = time() + 3600;
            $token = JwtToken::createToken(['email' => $request->email], $exp);
            return response()->json([
                'status' => true,
                'message' => 'OTP verified successfully.',
            ], 200)->cookie('resetPasswordToken', $token['token'], $exp);
        } catch (\Exception $e) {
            Log::critical($e->getMessage() . ' '  . $e->getFile() . ' ' . $e->getLine());
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred.',
            ], 500);
        }
    }
    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            if (!$request->cookie('resetPasswordToken')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid password request attempt',
                ]);
            }

            $decode = JwtToken::verifyToken($request->cookie('resetPasswordToken'));

            if ($decode['error']) {
                return response()->json([
                    'status' => false,
                    'message' => $decode['message'],
                ], 500);
            }
            if (!isset($decode['payload']->email)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token payload missing email',
                ], 400);
            }
            $user = User::whereEmail($decode['payload']->email)->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Password has been reset',
            ], 200)->withoutCookie('resetPasswordToken');
        } catch (\Exception $e) {
            Log::critical($e->getMessage() . ' '  . $e->getFile() . ' ' . $e->getLine());
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }
    public function logout()
    {
        return response()->json([
            'status' => true,
            'message' => 'User Logout successful'
        ])->withoutCookie('token');
    }
}

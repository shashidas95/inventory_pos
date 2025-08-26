<?php

namespace App\Http\Controllers\Auth;

use App\Models\Otp;
use App\Models\User;
use App\Helpers\JwtToken;
use App\Mail\SendOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;

class ResetPasswordController extends Controller
{
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
                    'errors' => ['Invalid password request attempt'],
                ], 422);
            }

            $decode = JwtToken::verifyToken($request->cookie('resetPasswordToken'));
            if ($decode['error']) {
                return response()->json([
                    'status' => false,
                    'message' => $decode['message'],
                ], 500);
            }

            $user = User::whereEmail($decode['payload']->email)->first();
            $user->password = $request->password;
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
}

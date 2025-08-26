<?php

namespace App\Http\Requests;

use App\Http\Requests\ApiBaseRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Otp; // You need to import the Otp model

class VerifyOtpRequest extends FormRequest
{
    use ApiBaseRequestTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
            'otp' => [
                'required',
                'digits:6',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $email = $this->input('email');

                    // The email field is guaranteed to exist and be a string at this point
                    $otpRecord = Otp::where('email', $email)
                        ->where('otp', $value)
                        ->where('status', false)
                        ->where('created_at', '>', now()->subMinutes(60))
                        ->first();

                    if (!$otpRecord) {
                        $fail('Invalid or expired OTP.');
                    }
                },
            ],
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $validator->errors()->all(),
        ], 422));
    }
}

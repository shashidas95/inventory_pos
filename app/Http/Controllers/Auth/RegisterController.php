<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Arr;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\RegisterRequest;

class RegisterController extends Controller
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
}

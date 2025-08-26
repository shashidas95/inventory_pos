<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProfileUpdateRequest;

class ProfileController extends Controller
{
    public function profile()
    {
        $data = Auth::user();

        return new UserResource($data);
    }

    public function profileUpdate(ProfileUpdateRequest $request)
    {
        try {
            $user = Auth::user();
            // Check if a user is authenticated
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            $validated = $request->validated();
            $userData = Arr::only($validated, ['name', 'email']);
            $profileData = Arr::only($validated, ['phone', 'address']);


            // Update the user's core data.
            $user->update($userData);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('avatars', 'public');
                $profileData['avatar'] = $path;
            }

            $user->profile->update($profileData);

            return response()->json([
                'status' => 200,
                'message' => "Profile updated successfully",
                'data' => new UserResource($user)
            ]);
        } catch (\Exception $e) {
            Log::critical($e->getMessage() . ' ' .  $e->getFile() . ' ' . $e->getLine());
            return response([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }
}

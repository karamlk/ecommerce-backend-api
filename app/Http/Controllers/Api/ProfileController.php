<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;
use App\Services\Profile\ProfileService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    protected $service;

    public function __construct(ProfileService $service)
    {
        $this->service = $service;
    }

    public function getProfile(Request $request)
    {
        return new ProfileResource(
            $this->service->getProfile($request->user())
        );
    }

    public function getProfilePhotos()
    {
        return response()->json([
            'photos' => $this->service->getProfilePhotos()
        ]);
    }

    public function updateProfilePhoto(Request $request)
    {
        $request->validate([
            'photo_id' => 'required|integer|exists:user_photos,id',
        ]);

        $user = $this->service->updateProfilePhoto(
            $request->user(),
            $request->photo_id
        );

        return new ProfileResource($user);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone_number' => 'sometimes|digits:10|unique:users,phone_number,' . $user->id,
            'location' => 'nullable|string|max:255',
            'current_password' => 'required_with:new_password',
            'new_password'     => 'required_with:current_password|confirmed|min:8',
        ]);

        try {
            $this->service->updateProfile($user, $validated);

            return response()->json([
                'message' => 'Profile updated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
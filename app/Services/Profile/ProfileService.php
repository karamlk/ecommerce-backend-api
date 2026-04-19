<?php

namespace App\Services\Profile;

use App\Models\User;
use App\Models\UserPhoto;
use Illuminate\Support\Facades\Hash;

class ProfileService
{
    public function getProfile(User $user)
    {
        return $user;
    }

    public function getProfilePhotos()
    {
        return UserPhoto::all()
            ->reject(fn($photo) => $photo->id == 11)
            ->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'profile_photo' => file_exists(public_path($photo->photo_url))
                        ? asset($photo->photo_url)
                        : 'https://placehold.co/150x150?text=profile_photo',
                ];
            })
            ->values();
    }

    public function updateProfilePhoto(User $user, $photoId)
    {
        $photo = UserPhoto::findOrFail($photoId);

        $user->update([
            'profile_photo' => $photo->photo_url,
        ]);

        return $user->fresh();
    }

    public function updateProfile(User $user, array $data)
    {
        // Handle password update
        if (isset($data['new_password'])) {

            if (!Hash::check($data['current_password'], $user->password)) {
                throw new \Exception('The current password is incorrect.');
            }

            $data['password'] = Hash::make($data['new_password']);
        }

        // Remove sensitive/unneeded fields
        unset($data['current_password'], $data['new_password']);

        $user->update($data);

        return true;
    }
}
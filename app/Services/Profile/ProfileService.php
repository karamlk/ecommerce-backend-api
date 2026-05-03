<?php

namespace App\Services\Profile;

use App\Aspects\ExecutionAspect;
use App\Models\User;
use App\Models\UserPhoto;
use Illuminate\Support\Facades\Hash;

class ProfileService
{
    public function __construct(private ExecutionAspect $execution) {}

    public function getProfile(User $user)
    {
        return $this->execution->run('ProfileService::getProfile',
            fn() => $user
        );
    }

    public function getProfilePhotos()
    {
        return $this->execution->run('ProfileService::getProfilePhotos',
            fn() => UserPhoto::all()
                ->reject(fn($photo) => $photo->id == 11)
                ->map(function ($photo) {
                    return [
                        'id'            => $photo->id,
                        'profile_photo' => file_exists(public_path($photo->photo_url))
                            ? asset($photo->photo_url)
                            : 'https://placehold.co/150x150?text=profile_photo',
                    ];
                })
                ->values()
        );
    }

    public function updateProfilePhoto(User $user, $photoId)
    {
        return $this->execution->run('ProfileService::updateProfilePhoto',
            function () use ($user, $photoId) {
                $photo = UserPhoto::findOrFail($photoId);

                $user->update([
                    'profile_photo' => $photo->photo_url,
                ]);

                return $user->fresh();
            }
        );
    }

    public function updateProfile(User $user, array $data)
    {
        return $this->execution->run('ProfileService::updateProfile',
            function () use ($user, $data) {

                if (isset($data['new_password'])) {
                    if (!Hash::check($data['current_password'], $user->password)) {
                        throw new \Exception('The current password is incorrect.');
                    }

                    $data['password'] = Hash::make($data['new_password']);
                }

                unset($data['current_password'], $data['new_password']);

                $user->update($data);

                return true;
            }
        );
    }
}
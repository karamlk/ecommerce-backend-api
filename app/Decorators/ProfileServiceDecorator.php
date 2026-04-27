<?php

namespace App\Decorators;

use App\Models\User;

class ProfileServiceDecorator extends BaseServiceDecorator
{
    public function getProfile(User $user)
    {
        return $this->run('getProfile', [$user]);
    }

    public function getProfilePhotos()
    {
        return $this->run('getProfilePhotos');
    }

    public function updateProfilePhoto(User $user, $photoId)
    {
        return $this->run('updateProfilePhoto', [$user, $photoId]);
    }

    public function updateProfile(User $user, array $data)
    {
        return $this->run('updateProfile', [$user, $data]);
    }
}

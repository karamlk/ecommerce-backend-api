<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    public function created(User $user): void
    {
        Log::channel('activity')->info('[USER REGISTERED]', [
            'user_id' => $user->id,
            'email'   => $user->email,
        ]);
    }

    public function updated(User $user): void
    {
        // only log security-sensitive changes
        if ($user->wasChanged('password')) {
            Log::channel('activity')->warning('[USER PASSWORD CHANGED]', [
                'user_id' => $user->id,
            ]);
        }

        if ($user->wasChanged('email')) {
            Log::channel('activity')->warning('[USER EMAIL CHANGED]', [
                'user_id'   => $user->id,
                'new_email' => $user->email,
            ]);
        }
    }

    public function deleted(User $user): void
    {
        Log::channel('activity')->warning('[USER DELETED]', [
            'user_id' => $user->id,
            'email'   => $user->email,
        ]);
    }
}
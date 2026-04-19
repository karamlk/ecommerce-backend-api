<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Jobs\SendOtpJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;
use App\Models\UserPhoto;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'location' => $validated['location'],
            'phone_number' => $validated['phone_number'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $defaultAvatar = UserPhoto::find(11);

        if ($defaultAvatar) {
            $user->profile_photo = $defaultAvatar->photo_url;
        }

        $otp = rand(100000, 999999);
        $hashedOtp = bcrypt($otp);
        $otpExpiry = Carbon::now()->addMinutes(10);

        $user->otp = $hashedOtp;
        $user->otp_expiry = $otpExpiry;
        $user->save();


        SendOtpJob::dispatch($user->email, (string)$otp);

        return response()->json([
            'message' => 'User created successfully. Please verify your email.',
            'email' => $user->email,
        ], 201);
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->otp_expiry < Carbon::now()) {
            return response()->json(['error' => 'OTP expired. Please request a new one.'], 400);
        }

        // Verify OTP
        if (!Hash::check($validated['otp'], $user->otp)) {
            return response()->json(['error' => 'Invalid OTP'], 400);
        }

        $user->is_verified = true;
        $user->otp = null;
        $user->otp_expiry = null;
        $user->save();

        $token = $user->createToken('otp-lar')->plainTextToken;

        return response()->json(['message' => 'Email verified successfully.', 'token' => $token]);
    }
}

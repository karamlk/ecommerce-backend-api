<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $identifier = $validated['identifier'];
        $password = $validated['password'];

        $user = User::where('email', $identifier)
            ->orWhere('phone_number', $identifier)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials.'], 401);
        }

        $token = $user->createToken('otp-lar1')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json(['message' => 'Logged out successfully.'], 200);
    }
}

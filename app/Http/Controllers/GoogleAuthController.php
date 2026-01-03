<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::firstOrCreate([
            'email' => $googleUser->getEmail(),
        ], [
            'name' => $googleUser->getName(),
            'password' => bcrypt(Str::random(24)),
            'email_verified_at' => now()
        ]);

        Auth::login($user);

        $token = $user->createToken('google-login')->plainTextToken;

        $redirectUrl = request('redirect_uri') ?? 'http://localhost:5005/callback';
        return redirect($redirectUrl . '?token=' . $token);
    }
}

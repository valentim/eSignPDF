<?php

namespace App\Presentation\Controllers\Auth;

use App\Presentation\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Domain\User\User;
use Laravel\Sanctum\HasApiTokens;

class SocialiteController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                ]);
            } else {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => encrypt('123456dummy')
                ]);
            }

            Auth::login($user);

            $token = $user->createToken('authToken')->plainTextToken;

            return redirect()->to('/auth?token=' . $token);
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Failed to login with Google, please try again.');
        }
    }
}


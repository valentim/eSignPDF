<?php

namespace App\Presentation\Controllers\Auth;

use App\Presentation\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Domain\User\User;
use Laravel\Sanctum\HasApiTokens;

class SocialiteController extends Controller
{
     /**
     * @OA\Get(
     *     path="/auth/google/redirect",
     *     tags={"auth"},
     *     summary="Redirect to Google authentication",
     *     description="Redirect the user to Google's OAuth page for authentication",
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to Google's OAuth page"
     *     )
     * )
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * @OA\Get(
     *     path="/auth/google/callback",
     *     tags={"auth"},
     *     summary="Handle Google OAuth callback",
     *     description="Handle the callback from Google's OAuth and log the user in or register them if they do not exist",
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to the application with an authentication token"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Failed to authenticate with Google"
     *     )
     * )
     */
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


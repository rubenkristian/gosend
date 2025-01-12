<?php

namespace App\Http\Controllers;

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class Auth extends Controller
{
    public function redirect(string $social)
    {
        return Socialite::driver($social)->redirect();
    }

    public function callback(string $social)
    {
        $socialUser = Socialite::driver($social)->user();

        $socialUserId = $socialUser->getId();

        $user = User::updateOrCreate(
            [
                'social_id' => "$social-$socialUserId"
            ],
            [
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
            ]
        );

        $user->assignRole('admin');

        \Illuminate\Support\Facades\Auth::login($user);

        return redirect()->route('home');
    }

    public function logout()
    {
        \Illuminate\Support\Facades\Auth::logout();
        return redirect()->route('login');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $email = $googleUser->getEmail();

            if (!str_ends_with($email, '@spxexpress.com')) {
                return redirect('/login')->withErrors([
                    'email' => 'Login gagal, pastikan memakai email kantor.',
                ]);
            }

            $user = User::where('provider_id', $googleUser->getId())->first();

            if (!$user) {
                $user = User::where('email', $email)->first();
                if ($user) {
                    $user->update([
                        'provider' => 'google',
                        'provider_id' => $googleUser->getId(),
                    ]);
                } else {
                    $user = User::create([
                        'name' => $googleUser->getName(),
                        'email' => $email,
                        'provider' => 'google',
                        'provider_id' => $googleUser->getId(),
                        'password' => null,
                        'role' => 'admin',
                    ]);
                }
            }

            Auth::login($user, true);

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            return redirect('/login')->withErrors([
                'email' => 'Login gagal, pastikan memakai email kantor.',
            ]);
        }
    }
}

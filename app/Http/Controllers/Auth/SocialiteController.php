<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function redirectFromCheckout(Event $event)
    {
        // Simpan event_id yang lagi di-checkout, dipakai lagi setelah callback
        session(['sso_checkout_event_id' => $event->id]);

        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            Log::error('Google OAuth callback failed: ' . $e->getMessage());

            return redirect()->route('home')
                ->with('error', 'Login dengan Google gagal, silakan coba lagi.');
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            } else {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => null,
                    'role' => 'customer',
                ]);
            }
        }

        Auth::login($user, remember: true);

        // Kalau tadi datang dari checkout, balik lagi ke event itu
        $eventId = session()->pull('sso_checkout_event_id');

        if ($eventId) {
            return redirect()->route('checkout.create', $eventId);
        }

        return redirect()->intended(route('home'));
    }
}
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
            
            // Cari atau buat user
            $findUser = User::where('google_id', $user->getId())->first();
            
            if($findUser){
                Auth::login($findUser);
                return redirect()->intended('/dashboard');
            }else{
                $newUser = User::create([
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'google_id'=> $user->getId(),
                    'password' => encrypt('my-google')
                ]);

                Auth::login($newUser);
                return redirect()->intended('/dashboard');
            }

        } catch (\Exception $e) {
            \Log::error('Google OAuth Error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Failed to login with Google');
        }
    }
}
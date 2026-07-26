<?php

namespace App\Http\Controllers;

use App\Models\Organizer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrganizerRegisterController extends Controller
{
    public function showForm()
    {
        return view('organizer.register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'logo'        => ['nullable', 'image', 'max:2048'],
            'email'       => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password'    => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('organizer-logos', 'public');
        }

        // Buat akun user baru dengan role organizer
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'organizer',
        ]);

        // Buat profil organizer, status default pending (harus di-approve superadmin)
        Organizer::create([
            'user_id'     => $user->id,
            'name'        => $validated['name'],
            'slug'        => Str::slug($validated['name']) . '-' . Str::random(5),
            'description' => $validated['description'] ?? null,
            'logo'        => $logoPath,
            'status'      => 'pending',
        ]);

        Auth::login($user);

        return redirect()
            ->route('home')
            ->with('success', 'Pendaftaran berhasil! Akun Anda sedang menunggu persetujuan Superadmin sebelum bisa mengelola event. Anda akan diberitahu setelah disetujui.');
    }
}
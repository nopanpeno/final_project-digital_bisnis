{{-- resources/views/organizer/register.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-6 py-16">
    <div class="glass rounded-2xl p-8 border border-white/20 shadow-lg">
        <h1 class="text-2xl font-bold mb-2">Daftar Sebagai Organizer</h1>
        <p class="text-slate-500 text-sm mb-8">
            Daftarkan HIMA / Kepanitiaan Anda untuk mulai membuat & mengelola event sendiri.
            Akun akan menunggu persetujuan Superadmin sebelum bisa aktif.
        </p>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3 mb-6">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('organizer.register.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama HIMA / Organizer</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                       placeholder="Contoh: HIMA Sistem Informasi">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi (opsional)</label>
                <textarea name="description" rows="3"
                       class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                       placeholder="Ceritakan sedikit tentang organisasi Anda">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Logo (opsional)</label>
                <input type="file" name="logo" accept="image/*"
                       class="w-full rounded-xl border border-slate-300 px-4 py-2.5 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-600">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                       placeholder="organizer@email.com">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required minlength="8"
                           class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition">
                Daftar Sekarang
            </button>
        </form>
    </div>
</div>
@endsection
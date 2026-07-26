@extends('layouts.admin')
@section('title', 'Tambah Partner - Admin')
@section('page_title', 'Tambah Partner')
@section('page_subtitle', 'Masukkan detail partner dan logo agar mitra tampil di admin panel.')

@section('content')
<div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm p-8 max-w-3xl">
    <form action="{{ route('partners.store') }}" method="POST" class="space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-3">Nama Partner</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition font-medium" placeholder="Contoh: Bank ABC">
            @error('name') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-3">URL Logo</label>
            <input type="url" name="logo_url" value="{{ old('logo_url') }}" required
                class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition font-medium" placeholder="https://example.com/logo.png">
            @error('logo_url') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
        </div>

        <div class="flex flex-wrap gap-3 items-center">
            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-2xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 active:scale-95 transition">Simpan Partner</button>
            <a href="{{ route('partners.index') }}" class="px-6 py-3 text-slate-500 font-bold hover:text-slate-800 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
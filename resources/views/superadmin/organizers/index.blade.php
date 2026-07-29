@extends('layouts.superadmin')

@section('content')
<div class="p-6 space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Organizer</h1>
        <p class="text-sm text-gray-500">Kelola kelayakan seluruh penyelenggara (HIMA/Kepanitiaan)</p>
    </div>

    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium">Nama Organizer</th>
                        <th class="text-left px-5 py-3 font-medium">Akun</th>
                        <th class="text-left px-5 py-3 font-medium">Total Event</th>
                        <th class="text-left px-5 py-3 font-medium">Status</th>
                        <th class="text-right px-5 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($organizers as $organizer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-800 font-medium">{{ $organizer->name }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $organizer->user->email ?? '-' }}</td>
                            <td class="px-5 py-3 text-gray-800">{{ $organizer->events_count }}</td>
                            <td class="px-5 py-3">
                                @php
                                    $badgeColor = match($organizer->status) {
                                        'approved' => 'bg-emerald-100 text-emerald-700',
                                        'suspended' => 'bg-red-100 text-red-700',
                                        default => 'bg-amber-100 text-amber-700',
                                    };
                                @endphp
                                <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $badgeColor }}">
                                    {{ ucfirst($organizer->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right space-x-2">
                                @if ($organizer->status !== 'approved')
                                    <form action="{{ route('superadmin.organizers.approve', $organizer->id) }}"
                                          method="POST" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="px-3 py-1.5 text-xs font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                                            Approve
                                        </button>
                                    </form>
                                @endif

                                @if ($organizer->status !== 'suspended')
                                    <form action="{{ route('superadmin.organizers.suspend', $organizer->id) }}"
                                          method="POST" class="inline-block"
                                          onsubmit="return confirm('Suspend organizer ini?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="px-3 py-1.5 text-xs font-medium rounded-lg bg-red-600 text-white hover:bg-red-700">
                                            Suspend
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-gray-400">
                                Belum ada organizer terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
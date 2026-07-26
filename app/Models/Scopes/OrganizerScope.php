<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Scope;

class OrganizerScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Belum login (halaman publik) -> jangan filter, biar semua event kelihatan
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();

        // Superadmin -> jangan filter, bisa lihat semua event dari semua organizer
        if ($user->isSuperadmin()) {
            return;
        }

        // Organizer -> filter cuma event miliknya sendiri
        if ($user->isOrganizer()) {
            $organizer = $user->organizer;

            if ($organizer) {
                $builder->where('organizer_id', $organizer->id);
            } else {
                // organizer login tapi belum punya row di tabel organizers (edge case)
                // aman-nya jangan tampilkan apa-apa
                $builder->whereRaw('1 = 0');
            }

            return;
        }

        // Role lain (customer, dst) -> jangan filter, publik lihat semua event
    }
}
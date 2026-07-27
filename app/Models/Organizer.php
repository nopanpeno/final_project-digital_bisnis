<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'name', 'slug', 'description', 'logo', 'status'])]
class Organizer extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function transactions()
    {
        return Transaction::whereIn('event_id', $this->events()->pluck('id'));
    }

    public function totalRevenue()
    {
        return $this->transactions()
            ->where('status', 'success') // cek dulu status apa yang dipakai pas transaksi berhasil
            ->sum('total_price');
    }

    public function totalTicketsSold()
    {
        return $this->transactions()
            ->where('status', 'success')
            ->count();
    }

    public function reviews()
{
    return Review::whereIn('event_id', $this->events()->pluck('id'));
}

public function averageRating()
{
    return $this->reviews()->avg('rating');
}

public function reviewCount()
{
    return $this->reviews()->count();
}
}
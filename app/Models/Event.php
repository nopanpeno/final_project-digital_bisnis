<?php

namespace App\Models;

use App\Models\Scopes\OrganizerScope;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'organizer_id', 'category_id', 'title', 'description', 'date',
        'location', 'price', 'stock', 'poster_path'
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new OrganizerScope);

        static::creating(function ($event) {
            if (auth()->check() && auth()->user()->role === 'organizer' && auth()->user()->organizer) {
                $event->organizer_id = auth()->user()->organizer->id;
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function organizer()
    {
        return $this->belongsTo(Organizer::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function reviews()
{
    return $this->hasMany(Review::class);
}

public function averageRating()
{
    return $this->reviews()->avg('rating');
}

public function hasSuccessfulTransactionFor($email)
{
    return $this->transactions()
        ->where('customer_email', $email)
        ->where('status', 'success')
        ->exists();
}

public function isReviewPeriodOpen()
{
    return $this->date <= now()->subDay();
}

}
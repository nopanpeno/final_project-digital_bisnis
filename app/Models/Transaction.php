<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'event_id', 'order_id', 'customer_name', 'customer_email', 'customer_phone', 'total_price', 'status', 'snap_token', 'reminder_sent_at',
    ];
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}

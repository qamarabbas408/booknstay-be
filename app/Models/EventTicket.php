<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventTicket extends Model
{
    use HasFactory;

    protected $fillable = ['event_id', 'name', 'price', 'quantity', 'sold'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function bookings() {
        return $this->hasMany(Booking::class, 'event_ticket_id');
    }
}

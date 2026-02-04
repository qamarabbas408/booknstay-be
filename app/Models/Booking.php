<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;


class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'bookable_id', 'bookable_type', 'booking_code',
        'check_in', 'check_out', 'event_date', 'guests_count',
        'rooms_count', 'tickets_count', 'total_price', 'status',
        'event_ticket_id',
    ];
      protected $casts = [
        'check_in'   => 'datetime',
        'check_out'  => 'datetime',
        'event_date' => 'datetime',
    ];

    /*
   In Laravel, we will use a Polymorphic Relationship. This allows a single bookings table to handle both Hotels and Events without creating two separate tables.
    */

    // The logic that links this booking to either a Hotel or Event
    public function bookable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticketTier()
    {
        return $this->belongsTo(EventTicket::class, 'event_ticket_id');
    }

    // Auto-generate code before saving (Booking Code)
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($booking) {
            $prefix = ($booking->bookable_type === 'App\Models\Hotel') ? 'H' : 'E';
            $booking->booking_code = 'BNS-'.$prefix.'-'.strtoupper(Str::random(6));
        });
    }
}

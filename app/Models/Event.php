<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Import this

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',           // The Vendor/Organizer
        'event_category_id', // Music, Tech, etc.
        'title',
        'description',
        'location',          // City, Country
        'venue',             // Specific Building (e.g. Wembley Arena)
        'start_time',    // Matches migration,
        'end_time',      // Matches migration,'
        'base_price',        // Price for tickets
        'image',             // Main banner image
        'status',            // active, pending, cancelled
        'is_featured',       // UI badge
        'is_trending',       // UI pulse animation
        'visibility',
        'highlights'

    ];

    // Ensure dates are treated as Carbon objects
    protected $casts = [
        'start_time' => 'datetime', // This is the fix for the format() error
        'end_time' => 'datetime',

        'is_featured' => 'boolean',
        'is_trending' => 'boolean',
        'highlights'=> 'array'
    ];

    /**
     * Relationships
     */

    // Who organized this?
    public function vendor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // What category does it belong to?
    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    // The bookings made for this event
    public function bookings()
    {
        return $this->morphMany(Booking::class, 'bookable');
    }

    /**
     * Business Logic: Calculate remaining tickets
     */
    public function getTicketsLeftAttribute()
    {
        // Sum the tickets_count from all 'confirmed' or 'completed' bookings
        $soldTickets = $this->bookings()
            ->whereIn('status', ['confirmed', 'completed'])
            ->sum('tickets_count');

        return max(0, $this->total_capacity - (int) $soldTickets);
    }

    /**
     * Business Logic: Is the event over?
     */
    public function getIsPastAttribute()
    {
        return $this->end_time ? $this->end_time->isPast() : $this->start_time->isPast();
    }

    public function tickets()
    {
        return $this->hasMany(EventTicket::class);
    }

    public function images() {
        return $this->hasMany(EventImage::class);
    }
}

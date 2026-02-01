<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    // Ensure dates are treated as Carbon objects
    protected $casts = [
        'start_time' => 'datetime', // This is the fix for the format() error
        'end_time' => 'datetime',

        'is_featured' => 'boolean',
        'is_trending' => 'boolean',
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
}

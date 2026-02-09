<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'address',
        'city',
        'country',
        'zip_code',
        'total_rooms',
        'max_capacity',
        'price_range',
        'status',
        'base_price',
        'star_rating',
        'tax_rate',    // Added
        'service_fee', // Added
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images()
    {
        return $this->hasMany(HotelImage::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getAverageRatingAttribute()
    {
        return round($this->reviews()->avg('rating'), 1) ?: 0;
    }

    public function getReviewCountAttribute()
    {
        return $this->reviews()->count();
    }

    public function bookings()
    {
        return $this->morphMany(Booking::class, 'bookable');
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function roomTypes()
    {
        return $this->hasMany(RoomType::class);
    }

    public function location()
    {
        return $this->morphOne(Location::class, 'locatable');
    }

    /**
     * Logic: Calculate the total price of the cheapest room including taxes/fees
     */
    public function getStartingPriceAttribute()
    {
        // 1. Find the cheapest active room
        $cheapestBase = $this->roomTypes()->where('status', 'active')->min('base_price');

        if (!$cheapestBase) return 0;

        // 2. Calculate Tax
        $taxAmount = ($cheapestBase * ($this->tax_rate / 100));

        // 3. Return Total (Base + Tax + Service Fee)
        return (float) ($cheapestBase + $taxAmount + $this->service_fee);
    }
}

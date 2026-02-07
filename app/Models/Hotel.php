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
}

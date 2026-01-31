<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\HotelImage;


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
        'base_price'
    ];


    public function owner () {
        return $this->belongsTo(User::class,'user_id');
    }

    public function images () {
        return $this->hasMany(HotelImage::class);
    }
}

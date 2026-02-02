<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Interest;
use App\Models\VendorProfile;
use App\Models\Hotel;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
         'role',   // Added
        'status', // Added
        'phone',  // Added'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function interests(){
        return $this->belongsToMany(Interest::class);
    }

    public function vendorProfile() {
        return $this->hasOne(VendorProfile::class);
    }

    public function hotels() {
        return $this->hasMany(Hotel::class);
    }
    public function events(){
        return $this->hasMany(Event::class);
    }
}


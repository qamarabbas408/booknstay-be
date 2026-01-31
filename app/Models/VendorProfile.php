<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class VendorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_name',
        'business_type',
        'business_email',
        'business_phone',
        'website',
    ];  


    public function user(){
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventImage extends Model
{
    use HasFactory;
    protected $fillable = [
        'image_path',
        'is_primary',
        'event_id'
    ];
    
}

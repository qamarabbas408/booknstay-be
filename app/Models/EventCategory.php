<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color_gradient'
    ];

    /**
     * Relationship: One category has many events.
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'locatable_id', 'locatable_type', 'country', 'city', 
        'full_address', 'zip_code', 'latitude', 'longitude'
    ];

    /**
     * Get the parent locatable model (Hotel or Event).
     */
    public function locatable()
    {
        return $this->morphTo();
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Interest;

class InterestController extends Controller
{
    public function index()
    {
        // Return all interests so React can build the selection buttons
        return response()->json(Interest::all());
    }
}
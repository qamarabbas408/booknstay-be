<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Amenity;

class AmentiyController extends Controller
{
    //
    public function index(Request $request){
        return response()->json(Amenity::all());
    }
}

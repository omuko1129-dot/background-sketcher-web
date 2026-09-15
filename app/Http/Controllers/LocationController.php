<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::select(
            'id',
            'user_id',
            'title',
            'description',
            DB::raw('ST_Y(location) as latitude'),
            DB::raw('ST_X(location) as longitude')
        )->get();

        return response()->json($locations);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $location = Location::create([
            'user_id' => 1,
            'title' => $request->title,
            'description' => $request->description,
            'location' => DB::raw("ST_GeomFromText('POINT({$request->longitude} {$request->latitude})', 4326)"),
        ]);

        return response()->json($location, 201);
    }
}

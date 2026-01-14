<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
public function updateLocation(Request $request) {
    $request->validate([
        'latitude' => 'required',
        'longitude' => 'required',
        'status_aktif' => 'required' // patroli, siaga, dll
    ]);

    $personnel = $request->user()->personnel; // Ambil personel dari token user
    
    $personnel->update([
        'latitude' => $request->latitude,
        'longitude' => $request->longitude,
        'status_aktif' => $request->status_aktif,
        'last_location_update' => now()
    ]);

    return response()->json(['message' => 'Lokasi diperbarui']);
}
}

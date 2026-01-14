<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
public function store(Request $request) {
    $request->validate([
        'judul_kejadian' => 'required',
        'tipe_laporan' => 'required',
        'latitude' => 'required',
        'longitude' => 'required',
        'foto_bukti' => 'nullable|image|max:2048'
    ]);

    $path = null;
    if ($request->hasFile('foto_bukti')) {
        $path = $request->file('foto_bukti')->store('reports', 'public');
    }

    $report = Report::create([
        'personnel_id' => $request->user()->personnel->id,
        'tipe_laporan' => $request->tipe_laporan,
        'judul_kejadian' => $request->judul_kejadian,
        'deskripsi' => $request->deskripsi ?? '-',
        'prioritas' => $request->prioritas ?? 'sedang',
        'latitude' => $request->latitude,
        'longitude' => $request->longitude,
        'foto_bukti' => $path,
        'status_penanganan' => 'menunggu'
    ]);

    return response()->json(['message' => 'Laporan terkirim', 'data' => $report]);
}
}

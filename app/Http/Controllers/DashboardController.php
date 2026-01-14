<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personnel;
use App\Models\Report;
use App\Models\Schedule; // Pastikan Model Schedule diimport
use App\Models\Instruction; // Pastikan Model Instruction diimport

class DashboardController extends Controller
{
    // Halaman Utama (Peta)
    public function index()
    {
        return view('dashboard.monitoring'); 
        // Note: Nanti file monitoring.blade.php kita update sedikit agar pakai layout
    }

    public function getLocations()
    {
        $personnels = Personnel::where('status_aktif', '!=', 'offline')
                        ->whereNotNull('latitude')
                        ->get(['id', 'nama_lengkap', 'pangkat', 'latitude', 'longitude', 'status_aktif']);
        return response()->json($personnels);
    }

    // 1. Data Laporan Digital
    public function laporan()
    {
        // Ambil laporan tipe 'aduan/kejadian'
        $laporan = Report::where('tipe_laporan', 'aduan/kejadian')
                         ->with('personnel')
                         ->latest()
                         ->get();
        return view('dashboard.laporan', compact('laporan'));
    }

    // 2. Checkpoint Log Report
    public function checkpoint()
    {
        // Ambil laporan tipe 'checkpoint'
        $checkpoints = Report::where('tipe_laporan', 'checkpoint')
                             ->with('personnel')
                             ->latest()
                             ->get();
        return view('dashboard.checkpoint', compact('checkpoints'));
    }

    // 3. Management Jadwal
    public function jadwal()
    {
        $jadwal = Schedule::with('personnel')->latest()->get();
        $personnels = Personnel::all(); // Untuk dropdown saat tambah jadwal
        return view('dashboard.jadwal', compact('jadwal', 'personnels'));
    }

    public function storeJadwal(Request $request)
    {
        Schedule::create($request->all());
        return back()->with('success', 'Jadwal berhasil ditambahkan');
    }

    // 4. Instruksi Personel
    public function instruksi()
    {
        $instruksi = Instruction::latest()->get();
        return view('dashboard.instruksi', compact('instruksi'));
    }

    public function storeInstruksi(Request $request)
    {
        Instruction::create([
            'judul' => $request->judul,
            'isi_instruksi' => $request->isi_instruksi,
            'personnel_id' => null // Null berarti broadcast ke semua
        ]);
        return back()->with('success', 'Instruksi dikirim ke semua personel');
    }
}
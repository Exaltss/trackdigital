<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personnel;
use App\Models\Report;
use App\Models\Schedule;
use App\Models\Instruction;

class DashboardController extends Controller
{
    // 1. MONITORING
    public function index()
    {
        return view('dashboard.monitoring');
    }

    public function getLocations()
    {
        $personnels = Personnel::where('status_aktif', '!=', 'offline')
                        ->whereNotNull('latitude')
                        ->get(['id', 'nama_lengkap', 'pangkat', 'latitude', 'longitude', 'status_aktif']);
        return response()->json($personnels);
    }

    // 2. LAPORAN
    public function laporan()
    {
        $laporan = Report::where('tipe_laporan', 'aduan/kejadian')
                         ->with('personnel')->latest()->get();
        return view('dashboard.laporan', compact('laporan'));
    }

    // 3. CHECKPOINT
    public function checkpoint()
    {
        $checkpoints = Report::where('tipe_laporan', 'checkpoint')
                             ->with('personnel')->latest()->get();
        return view('dashboard.checkpoint', compact('checkpoints'));
    }

    // 4. JADWAL
    public function jadwal()
    {
        $jadwal = Schedule::with('personnel')->latest()->get();
        $personnels = Personnel::all();
        return view('dashboard.jadwal', compact('jadwal', 'personnels'));
    }

    public function storeJadwal(Request $request)
    {
        Schedule::create($request->all());
        return back()->with('success', 'Jadwal berhasil ditambahkan');
    }

    // 5. INSTRUKSI
    public function instruksi()
    {
        $personnels = Personnel::all();
        $instruksi = Instruction::with('personnel')->latest()->get();
        return view('dashboard.instruksi', compact('instruksi', 'personnels'));
    }

    public function storeInstruksi(Request $request)
    {
        if ($request->has('is_darurat')) {
            Instruction::create([
                'judul' => 'PERINGATAN DARURAT',
                'tipe_instruksi' => 'darurat',
                'isi_instruksi' => 'SEGERA MERAPAT KE MARKAS / TITIK AMAN. STATUS SIAGA 1.',
                'personnel_id' => null, 
            ]);
            return back()->with('success', 'Sinyal Darurat dikirim!');
        }

        $data = [
            'judul' => $request->judul,
            'tipe_instruksi' => $request->tipe_instruksi,
            'isi_instruksi' => $request->isi_instruksi,
            'personnel_id' => $request->personnel_id == 'all' ? null : $request->personnel_id,
        ];

        if ($request->tipe_instruksi == 'lokasi') {
            $request->validate(['latitude' => 'required', 'longitude' => 'required']);
            $data['latitude'] = $request->latitude;
            $data['longitude'] = $request->longitude;
        }

        Instruction::create($data);
        return back()->with('success', 'Instruksi berhasil dikirim');
    }

    public function destroyInstruksi($id)
    {
        Instruction::findOrFail($id)->delete();
        return back()->with('success', 'Instruksi dihapus.');
    }

    // [BARU] API SIMULASI NOTIFIKASI
    public function getLatestInstruction()
    {
        $latest = Instruction::with('personnel')->latest()->first();
        
        return response()->json([
            'id' => $latest ? $latest->id : 0,
            'judul' => $latest ? $latest->judul : '-',
            'isi' => $latest ? $latest->isi_instruksi : '-',
            'target' => $latest && $latest->personnel ? $latest->personnel->nama_lengkap : 'SEMUA PERSONEL',
            'tipe' => $latest ? $latest->tipe_instruksi : 'normal',
            'waktu' => $latest ? $latest->created_at->diffForHumans() : 'Baru saja'
        ]);
    }
}
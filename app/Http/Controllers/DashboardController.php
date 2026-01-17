<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personnel;
use App\Models\Report;
use App\Models\Schedule;
use App\Models\Instruction;

class DashboardController extends Controller
{
    // ==========================================
    // 1. HALAMAN MONITORING (PETA UTAMA)
    // ==========================================
    public function index()
    {
        return view('dashboard.monitoring');
    }

    // API JSON untuk Peta (Dipanggil via AJAX)
    public function getLocations()
    {
        // Hanya ambil personel yang aktif (tidak offline)
        $personnels = Personnel::where('status_aktif', '!=', 'offline')
                        ->whereNotNull('latitude')
                        ->get(['id', 'nama_lengkap', 'pangkat', 'latitude', 'longitude', 'status_aktif']);
        return response()->json($personnels);
    }

    // ==========================================
    // 2. HALAMAN DATA LAPORAN DIGITAL
    // ==========================================
    public function laporan()
    {
        $laporan = Report::where('tipe_laporan', 'aduan/kejadian')
                         ->with('personnel')
                         ->latest()
                         ->get();
        return view('dashboard.laporan', compact('laporan'));
    }

    // ==========================================
    // 3. HALAMAN CHECKPOINT LOG
    // ==========================================
    public function checkpoint()
    {
        $checkpoints = Report::where('tipe_laporan', 'checkpoint')
                             ->with('personnel')
                             ->latest()
                             ->get();
        return view('dashboard.checkpoint', compact('checkpoints'));
    }

    // ==========================================
    // 4. HALAMAN JADWAL PERSONEL
    // ==========================================
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

    // ==========================================
    // 5. HALAMAN INSTRUKSI PERSONEL
    // ==========================================
    public function instruksi()
    {
        $personnels = Personnel::all();
        // Ambil data instruksi terbaru beserta data personel targetnya
        $instruksi = Instruction::with('personnel')->latest()->get();
        return view('dashboard.instruksi', compact('instruksi', 'personnels'));
    }

    // Simpan Instruksi Baru
    public function storeInstruksi(Request $request)
    {
        // A. JIKA TOMBOL DARURAT DITEKAN
        if ($request->has('is_darurat')) {
            Instruction::create([
                'judul' => 'PERINGATAN DARURAT',
                'tipe_instruksi' => 'darurat',
                'isi_instruksi' => 'SEGERA MERAPAT KE MARKAS / TITIK AMAN. STATUS SIAGA 1.',
                'personnel_id' => null, // null = Broadcast ke semua
            ]);
            return back()->with('success', 'Sinyal Darurat telah dikirim ke SEMUA personel!');
        }

        // B. JIKA INSTRUKSI BIASA
        $data = [
            'judul' => $request->judul,
            'tipe_instruksi' => $request->tipe_instruksi,
            'isi_instruksi' => $request->isi_instruksi,
            // Jika pilih 'all', simpan null. Jika spesifik, simpan ID-nya.
            'personnel_id' => $request->personnel_id == 'all' ? null : $request->personnel_id,
        ];

        // Validasi Koordinat jika tipe instruksi adalah 'lokasi'
        if ($request->tipe_instruksi == 'lokasi') {
            $request->validate([
                'latitude' => 'required',
                'longitude' => 'required',
            ]);
            $data['latitude'] = $request->latitude;
            $data['longitude'] = $request->longitude;
        }

        Instruction::create($data);

        return back()->with('success', 'Instruksi berhasil dikirim');
    }

    // Hapus / Batalkan Instruksi
    public function destroyInstruksi($id)
    {
        $instruksi = Instruction::findOrFail($id);
        $instruksi->delete();
        return back()->with('success', 'Instruksi berhasil dibatalkan dan dihapus.');
    }
}
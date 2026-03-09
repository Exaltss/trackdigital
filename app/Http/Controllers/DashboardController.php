<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personnel;
use App\Models\Report;
use App\Models\Schedule;
use App\Models\Instruction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
// Import Library PDF
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    // --- VIEW: HALAMAN UTAMA MONITORING ---
    public function index() 
    { 
        return view('dashboard.monitoring'); 
    }

    // --- VIEW: DAFTAR LAPORAN DENGAN FILTER ---
    public function laporan(Request $request) 
    {
        $query = Report::where('tipe_laporan', 'aduan/kejadian')->with('personnel');
        $personnels = Personnel::all();

        // Filter Tanggal
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Filter Spesifik Personel (Rekap 1 orang)
        if ($request->filled('personnel_id') && $request->personnel_id != 'all') {
            $query->where('personnel_id', $request->personnel_id);
        }

        $laporan = $query->latest()->get();

        return view('dashboard.laporan', compact('laporan', 'personnels'));
    }

    // --- ACTION: EXPORT KE PDF (DENGAN REKAP GAMBAR & FILTER) ---
    public function exportPdf(Request $request)
    {
        $query = Report::where('tipe_laporan', 'aduan/kejadian')->with('personnel');

        // Samakan filter dengan yang ada di halaman utama
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $targetName = "Semua Personel";
        if ($request->filled('personnel_id') && $request->personnel_id != 'all') {
            $query->where('personnel_id', $request->personnel_id);
            $p = Personnel::find($request->personnel_id);
            $targetName = $p ? $p->nama_lengkap : "Personel";
        }

        $laporan = $query->latest()->get();

        $data = [
            'title'   => 'REKAPITULASI LAPORAN ADUAN DIGITAL',
            'date'    => date('d/m/Y H:i'),
            'target'  => $targetName,
            'periode' => ($request->from_date ?? 'Awal') . ' s/d ' . ($request->to_date ?? 'Sekarang'),
            'laporan' => $laporan
        ];

        // Load View PDF dan set ke Landscape agar kolom gambar lega
        $pdf = Pdf::loadView('dashboard.pdf_laporan', $data)->setPaper('a4', 'landscape');
        
        $filename = 'Rekap_Laporan_' . str_replace(' ', '_', $targetName) . '_' . date('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }

    // --- FITUR REAL-TIME: CEK LAPORAN BARU ---
    public function checkNotification()
    {
        $count = Report::where('tipe_laporan', 'aduan/kejadian')
                       ->where('status_penanganan', 'menunggu konfirmasi')
                       ->count();
        return response()->json(['unread_count' => $count]);
    }

    // --- MANAJEMEN DATA DASHBOARD ---
    public function checkpoint() {
        $checkpoints = Report::where('tipe_laporan', 'checkpoint')->with('personnel')->latest()->get();
        return view('dashboard.checkpoint', compact('checkpoints'));
    }

    public function jadwal() {
        $jadwal = Schedule::with('personnel')->latest()->get();
        $personnels = Personnel::all();
        return view('dashboard.jadwal', compact('jadwal', 'personnels'));
    }

    public function instruksi() {
        $personnels = Personnel::all();
        $instruksi = Instruction::with('personnel')->latest()->get();
        return view('dashboard.instruksi', compact('instruksi', 'personnels'));
    }

    public function storeJadwal(Request $request) {
        $validated = $request->validate(['personnel_id'=>'required|exists:personnels,id','tanggal'=>'required|date','shift'=>'required|string','lokasi_target'=>'required|string','latitude'=>'nullable','longitude'=>'nullable']);
        Schedule::create($validated); 
        return back()->with('success', 'Jadwal berhasil ditambahkan');
    }

    public function destroyJadwal($id) { 
        Schedule::findOrFail($id)->delete(); 
        return back()->with('success', 'Jadwal dihapus'); 
    }

    public function storeInstruksi(Request $request) {
        $v = $request->validate(['judul'=>'required','tipe_instruksi'=>'required','isi_instruksi'=>'required']);
        Instruction::create([
            ...$v, 
            'latitude' => $request->latitude, 
            'longitude' => $request->longitude, 
            'personnel_id' => $request->personnel_id == 'all' ? null : $request->personnel_id
        ]);
        return back()->with('success', 'Instruksi berhasil dikirim');
    }

    public function destroyInstruksi($id) { 
        Instruction::findOrFail($id)->delete(); 
        return back()->with('success', 'Instruksi dihapus'); 
    }

    public function updateStatusLaporan($id, $status) {
        Report::findOrFail($id)->update(['status_penanganan' => $status]);
        return back()->with('success', 'Status laporan diperbarui');
    }

    public function destroyLaporan($id) {
        $l = Report::findOrFail($id);
        if($l->foto_bukti) Storage::disk('public')->delete($l->foto_bukti);
        $l->delete(); 
        return back()->with('success', 'Laporan berhasil dihapus');
    }

    // =========================================================================
    // API METHODS UNTUK FLUTTER
    // =========================================================================

    public function getLocations() { 
        return response()->json(Personnel::where('status_aktif','!=','offline')->whereNotNull('latitude')->get()); 
    }

    public function getLatestInstruction(Request $request) {
        $pId = $request->user()->personnel->id;
        $l = Instruction::where(function($q) use ($pId) {
            $q->whereNull('personnel_id')->orWhere('personnel_id', $pId);
        })->latest()->first();

        return response()->json([
            'id' => $l ? $l->id : 0, 
            'judul' => $l ? $l->judul : '-', 
            'isi' => $l ? $l->isi_instruksi : '-', 
            'tipe' => $l ? $l->tipe_instruksi : 'normal', 
            'latitude' => $l ? $l->latitude : null, 
            'longitude' => $l ? $l->longitude : null
        ]);
    }

    public function getJadwalMobile(Request $request) { 
        return response()->json([
            'success' => true, 
            'data' => Schedule::where('personnel_id', $request->user()->personnel->id)->latest()->take(10)->get()
        ]); 
    }

    public function getRingkasanLaporan(Request $request) {
        $pId = $request->user()->personnel->id;
        return response()->json([
            'success' => true, 
            'laporan_count' => Report::where('personnel_id', $pId)->where('tipe_laporan', 'aduan/kejadian')->count(), 
            'checkpoint_count' => Report::where('personnel_id', $pId)->where('tipe_laporan', 'checkpoint')->count()
        ]);
    }
}
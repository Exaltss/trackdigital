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

    // --- FITUR BARU: UPDATE FOTO PROFIL DARI MOBILE ---
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = $request->user();
        $personnel = $user->personnel;

        if (!$personnel) {
            return response()->json(['message' => 'Data personel tidak ditemukan'], 404);
        }

        if ($request->hasFile('foto')) {
            // 1. Hapus foto lama jika ada di storage
            if ($personnel->foto_profil) {
                Storage::disk('public')->delete($personnel->foto_profil);
            }

            // 2. Simpan foto baru ke folder public/profile_photos
            $path = $request->file('foto')->store('profile_photos', 'public');
            
            // 3. Update path di database
            $personnel->update(['foto_profil' => $path]);

            return response()->json([
                'message' => 'Foto profil berhasil diperbarui',
                'url' => asset('storage/' . $path)
            ]);
        }

        return response()->json(['message' => 'Gagal mengunggah foto'], 400);
    }

    // --- VIEW: DAFTAR LAPORAN DENGAN FILTER ---
    public function laporan(Request $request) 
    {
        $query = Report::where('tipe_laporan', 'aduan/kejadian')->with('personnel');
        $personnels = Personnel::all();

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        if ($request->filled('personnel_id') && $request->personnel_id != 'all') {
            $query->where('personnel_id', $request->personnel_id);
        }

        $laporan = $query->latest()->get();
        return view('dashboard.laporan', compact('laporan', 'personnels'));
    }

    // --- ACTION: EXPORT KE PDF (ADUAN) ---
    public function exportPdf(Request $request)
    {
        $query = Report::where('tipe_laporan', 'aduan/kejadian')->with('personnel');

        if ($request->filled('from_date')) { $query->whereDate('created_at', '>=', $request->from_date); }
        if ($request->filled('to_date')) { $query->whereDate('created_at', '<=', $request->to_date); }

        $targetName = "Semua Personel";
        if ($request->filled('personnel_id') && $request->personnel_id != 'all') {
            $query->where('personnel_id', $request->personnel_id);
            $p = Personnel::find($request->personnel_id);
            $targetName = $p ? $p->nama_lengkap : "Personel";
        }

        $data = [
            'title'   => 'REKAPITULASI LAPORAN ADUAN DIGITAL',
            'date'    => date('d/m/Y H:i'),
            'target'  => $targetName,
            'periode' => ($request->from_date ?? 'Awal') . ' s/d ' . ($request->to_date ?? 'Sekarang'),
            'laporan' => $query->latest()->get()
        ];

        $pdf = Pdf::loadView('dashboard.pdf_laporan', $data)->setPaper('a4', 'landscape');
        return $pdf->download('Rekap_Laporan_' . str_replace(' ', '_', $targetName) . '_' . date('Ymd_His') . '.pdf');
    }

    public function checkNotification()
    {
        $count = Report::where('tipe_laporan', 'aduan/kejadian')->where('status_penanganan', 'menunggu konfirmasi')->count();
        return response()->json(['unread_count' => $count]);
    }

    // --- MANAJEMEN DATA CHECKPOINT ---
    public function checkpoint(Request $request) {
        $query = Report::where('tipe_laporan', 'checkpoint')->with('personnel');
        $personnels = Personnel::all();

        if ($request->filled('from_date')) { $query->whereDate('created_at', '>=', $request->from_date); }
        if ($request->filled('to_date')) { $query->whereDate('created_at', '<=', $request->to_date); }
        if ($request->filled('personnel_id') && $request->personnel_id != 'all') {
            $query->where('personnel_id', $request->personnel_id);
        }

        $checkpoints = $query->latest()->get();
        return view('dashboard.checkpoint', compact('checkpoints', 'personnels'));
    }

    // --- EXPORT PDF KHUSUS CHECKPOINT ---
    public function exportCheckpointPdf(Request $request)
    {
        $query = Report::where('tipe_laporan', 'checkpoint')->with('personnel');

        if ($request->filled('from_date')) { $query->whereDate('created_at', '>=', $request->from_date); }
        if ($request->filled('to_date')) { $query->whereDate('created_at', '<=', $request->to_date); }

        $targetName = "Semua Personel";
        if ($request->filled('personnel_id') && $request->personnel_id != 'all') {
            $query->where('personnel_id', $request->personnel_id);
            $p = Personnel::find($request->personnel_id);
            $targetName = $p ? $p->nama_lengkap : "Personel";
        }

        $data = [
            'title'   => 'REKAPITULASI PERISTIWA CHECKPOINT PERSONEL',
            'date'    => date('d/m/Y H:i'),
            'target'  => $targetName,
            'periode' => ($request->from_date ?? 'Awal') . ' s/d ' . ($request->to_date ?? 'Sekarang'),
            'laporan' => $query->latest()->get()
        ];

        $pdf = Pdf::loadView('dashboard.pdf_checkpoint', $data)->setPaper('a4', 'landscape');
        
        $filename = 'Rekap_Peristiwa_' . str_replace(' ', '_', $targetName) . '_' . date('Ymd_His') . '.pdf';
        return $pdf->download($filename);
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
        $validated = $request->validate([
            'personnel_id'=>'required|exists:personnels,id',
            'tanggal'=>'required|date',
            'shift'=>'required|string',
            'lokasi_target'=>'required|string',
            'latitude'=>'nullable',
            'longitude'=>'nullable'
        ]);
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
            'personnel_id' => ($request->personnel_id == 'all' || !$request->personnel_id) ? null : $request->personnel_id
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
    // API PETA WEB & MOBILE
    // =========================================================================

    public function getLocations() { 
        $today = \Carbon\Carbon::today();

        $personnels = Personnel::where('status_aktif', '!=', 'offline')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with(['schedules' => function ($query) use ($today) {
                $query->whereDate('tanggal', $today)->orderBy('id', 'asc');
            }])
            ->get()
            ->map(function($p) {
                $p->latest_instruction = Instruction::where(function($q) use ($p) {
                        $q->whereNull('personnel_id')->orWhere('personnel_id', $p->id);
                    })
                    ->latest()
                    ->first();
                return $p;
            });

        return response()->json($personnels); 
    }

    public function getCheckpointsJson() {
        $checkpoints = Report::where('tipe_laporan', 'checkpoint')->with('personnel')->latest()->take(200)->get();
        return response()->json($checkpoints);
    }

    // =========================================================================
    // API KHUSUS MOBILE (FLUTTER)
    // =========================================================================

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
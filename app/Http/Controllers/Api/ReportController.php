<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Menampilkan riwayat laporan khusus personel yang sedang login.
     */
    public function index(Request $request)
    {
        try {
            $personnelId = $request->user()->personnel->id;

            // PERBAIKAN: Mengambil laporan aduan DAN checkpoint milik personel tersebut
            $reports = Report::where('personnel_id', $personnelId)
                ->whereIn('tipe_laporan', ['aduan/kejadian', 'checkpoint'])
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $reports
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan laporan baru dari aplikasi Mobile.
     */
    public function store(Request $request) 
    {
        try {
            // 1. Validasi Input dari Mobile
            $request->validate([
                'judul_kejadian' => 'required|string',
                'tipe_laporan'   => 'required|string',
                'latitude'       => 'required',
                'longitude'      => 'required',
                'foto_bukti'     => 'nullable|image|max:5120' // Maksimal 5MB
            ]);

            // 2. Cek Relasi Personel (Keamanan)
            if (!$request->user()->personnel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda belum terhubung ke data Personel.'
                ], 403);
            }

            // 3. Proses Simpan Foto ke Storage
            $path = null;
            if ($request->hasFile('foto_bukti')) {
                // File akan disimpan di folder: storage/app/public/reports
                $path = $request->file('foto_bukti')->store('reports', 'public');
            }

            // 4. Insert ke Database
            $report = Report::create([
                'personnel_id'      => $request->user()->personnel->id,
                'tipe_laporan'      => $request->tipe_laporan,
                'judul_kejadian'    => $request->judul_kejadian,
                'deskripsi'         => $request->deskripsi ?? '-',
                'prioritas'         => $request->prioritas ?? 'sedang',
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
                'foto_bukti'        => $path,
                'status_penanganan' => 'menunggu konfirmasi' 
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dikirim ke Command Center',
                'data'    => $report
            ], 201);

        } catch (\Exception $e) {
            Log::error("Gagal API Laporan: " . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Eror Server: ' . $e->getMessage()
            ], 500);
        }
    }
}
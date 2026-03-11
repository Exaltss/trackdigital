<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Peristiwa Checkpoint</title>
    <style>
        /* Mengatur halaman menjadi Landscape agar semua kolom muat dengan rapi */
        @page { margin: 0.8cm; }
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 11px;
            color: #555;
        }
        .info-rekap {
            width: 100%;
            margin-bottom: 15px;
        }
        .info-rekap td {
            padding: 2px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* Menjaga lebar kolom tetap konsisten */
        }
        .data-table th, .data-table td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }
        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
            text-transform: uppercase;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .coords {
            font-family: "Courier New", Courier, monospace;
            font-size: 9px;
            color: #0044cc;
            font-weight: bold;
        }
        .foto-img {
            max-width: 100px;
            height: auto;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 9px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>{{ $title }}</h2>
        <p>Sistem Pantau Keamanan & GPS Realtime - Polres Tulungagung</p>
    </div>

    <table class="info-rekap">
        <tr>
            <td width="15%"><strong>Periode Laporan</strong></td>
            <td width="35%">: {{ $periode }}</td>
            <td width="15%"><strong>Waktu Cetak</strong></td>
            <td width="35%">: {{ $date }}</td>
        </tr>
        <tr>
            <td><strong>Target Personel</strong></td>
            <td>: {{ $target }}</td>
            <td><strong>Total Data</strong></td>
            <td>: {{ $laporan->count() }} Peristiwa</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="14%">Waktu & Petugas</th>
                <th width="18%">Peristiwa & Koordinat GPS</th> <th width="30%">Detail Keterangan / Kronologi</th>
                <th width="12%">Prioritas</th>
                <th width="22%">Dokumentasi Foto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporan as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                
                <td>
                    <strong>{{ $item->created_at->format('d/m/Y') }}</strong><br>
                    Jam: {{ $item->created_at->format('H:i') }} WIB<br><br>
                    <u>{{ $item->personnel->nama_lengkap ?? 'Unknown' }}</u>
                </td>

                <td>
                    <strong>{{ $item->judul_kejadian ?? $item->judul_laporan ?? 'Peristiwa' }}</strong><br><br>
                    <span class="coords">
                        Lat: {{ $item->latitude }}<br>
                        Lng: {{ $item->longitude }}
                    </span>
                </td>

                <td>
                    {!! nl2br(e($item->deskripsi ?? $item->isi_laporan ?? '-')) !!}
                </td>

                <td class="text-center">
                    @php
                        $prio = strtolower($item->prioritas ?? '');
                        $desk = strtolower($item->deskripsi ?? '');
                    @endphp

                    @if($prio == 'tinggi' || str_contains($desk, 'tinggi'))
                        <b style="color: red;">TINGGI</b>
                    @elseif($prio == 'rendah' || str_contains($desk, 'rendah'))
                        <b style="color: green;">RENDAH</b>
                    @elseif(str_contains($desk, 'aman'))
                        <b style="color: blue;">AMAN</b>
                    @else
                        <b>NORMAL</b>
                    @endif
                </td>

                <td class="text-center">
                    @if($item->foto_bukti)
                        @php
                            // Mengonversi image ke Base64 agar muncul stabil di PDF
                            $path = storage_path('app/public/' . $item->foto_bukti);
                            if(file_exists($path)){
                                $type = pathinfo($path, PATHINFO_EXTENSION);
                                $data = file_get_contents($path);
                                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                            } else {
                                $base64 = null;
                            }
                        @endphp
                        
                        @if($base64)
                            <img src="{{ $base64 }}" class="foto-img">
                        @else
                            <small style="color: #999 italic;">File tidak ditemukan</small>
                        @endif
                    @else
                        <small style="color: #ccc;">(Tanpa Foto)</small>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak otomatis oleh Sistem Digital Monitoring Polres Tulungagung pada {{ date('d F Y') }}</p>
    </div>

</body>
</html>
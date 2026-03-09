<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Laporan Digital</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #444; padding: 6px; text-align: left; word-wrap: break-word; vertical-align: top; }
        th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; }
        .footer { margin-top: 40px; text-align: right; font-size: 11px; }
        .img-bukti { width: 100px; height: auto; border: 1px solid #ccc; border-radius: 4px; display: block; margin: 0 auto; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin: 0;">POLRES TULUNGAGUNG</h2>
        <h3 style="margin: 5px 0;">{{ $title }}</h3>
        <p style="margin: 0;">Target Rekap: <b>{{ $target }}</b> | Periode: {{ $periode }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 75px;">Waktu Kejadian</th>
                <th style="width: 110px;">Nama Personel</th>
                <th style="width: 100px;">Judul Laporan</th>
                <th>Kronologi / Deskripsi Kejadian</th>
                <th style="width: 110px;" class="text-center">Foto Bukti</th>
                <th style="width: 80px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporan as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->created_at->format('d/m/Y H:i') }} WIB</td>
                <td>{{ $item->personnel->nama_lengkap }}<br><small>({{ $item->personnel->pangkat }})</small></td>
                <td>{{ $item->judul_kejadian }}</td>
                <td>{{ $item->deskripsi }}</td>
                <td class="text-center">
                    @if($item->foto_bukti)
                        @php
                            $path = public_path('storage/' . $item->foto_bukti);
                        @endphp
                        @if(file_exists($path))
                            <img src="{{ $path }}" class="img-bukti">
                        @else
                            <small style="color: red;">Gambar Tidak Ditemukan</small>
                        @endif
                    @else
                        <small class="text-muted" style="font-style: italic;">Tanpa Lampiran Foto</small>
                    @endif
                </td>
                <td class="text-center">{{ strtoupper($item->status_penanganan) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Tulungagung, {{ date('d F Y') }}</p>
        <p>Dicetak pada: {{ $date }} WIB</p>
        <br><br><br><br>
        <p><b>__________________________</b></p>
        <p>Admin Command Center</p>
    </div>
</body>
</html>
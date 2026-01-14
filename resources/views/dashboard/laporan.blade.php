@extends('layouts.admin')

@section('title', 'Data Laporan Digital')
@section('header-title', 'Data Laporan Masuk')

@section('content')
<div class="card card-custom p-4 bg-white">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Waktu</th>
                    <th>Pelapor</th>
                    <th>Detail Kejadian</th>
                    <th>Lokasi (Lat, Long)</th>
                    <th>Status</th>
                    <th>Opsi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($laporan as $item)
                <tr>
                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <div class="fw-bold">{{ $item->personnel->nama_lengkap }}</div>
                        <small class="text-muted">{{ $item->personnel->nrp }}</small>
                    </td>
                    <td>
                        <span class="fw-bold text-dark">{{ $item->judul_kejadian }}</span><br>
                        <small class="text-muted">{{ Str::limit($item->deskripsi, 50) }}</small>
                    </td>
                    <td>{{ $item->latitude }}, {{ $item->longitude }}</td>
                    <td>
                        @if($item->status_penanganan == 'menunggu')
                            <span class="badge bg-warning text-dark">Menunggu</span>
                        @elseif($item->status_penanganan == 'selesai')
                            <span class="badge bg-success">Selesai</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary">Detail</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
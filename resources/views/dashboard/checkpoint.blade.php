@extends('layouts.admin')

@section('title', 'Checkpoint Log Report')
@section('header-title', 'Laporan Lokasi Checkpoint')

@section('content')
<div class="card card-custom p-4 bg-white">
    <table class="table table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>Waktu</th>
                <th>Personel</th>
                <th>Lokasi Checkpoint</th>
                <th>Keterangan</th>
                <th>Prioritas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checkpoints as $item)
            <tr>
                <td>{{ $item->created_at->format('H:i - d M Y') }}</td>
                <td>{{ $item->personnel->nama_lengkap }}</td>
                <td>
                    <i class="bi bi-geo-alt-fill text-danger"></i> 
                    {{ $item->judul_kejadian }}
                </td>
                <td>{{ $item->deskripsi }}</td>
                <td>
                    @if($item->prioritas == 'tinggi')
                        <span class="badge bg-danger">TINGGI</span>
                    @else
                        <span class="badge bg-info">NORMAL</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
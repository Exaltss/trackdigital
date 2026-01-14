@extends('layouts.admin')

@section('title', 'Jadwal Personel')
@section('header-title', 'Manajemen Penjadwalan Personel')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-custom p-3 bg-white mb-3">
            <h6 class="fw-bold mb-3">Buat Jadwal Baru</h6>
            <form action="{{ route('jadwal.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Personel</label>
                    <select name="personnel_id" class="form-select">
                        @foreach($personnels as $p)
                            <option value="{{ $p->id }}">{{ $p->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Shift</label>
                    <select name="shift" class="form-select">
                        <option value="Pagi (08:00 - 16:00)">Pagi</option>
                        <option value="Siang (16:00 - 00:00)">Siang</option>
                        <option value="Malam (00:00 - 08:00)">Malam</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Lokasi Target</label>
                    <input type="text" name="lokasi_target" class="form-control" placeholder="Contoh: Pasar Wage" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Simpan Jadwal</button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card card-custom p-3 bg-white">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal & Shift</th>
                        <th>Personel</th>
                        <th>Target</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jadwal as $j)
                    <tr>
                        <td>
                            {{ $j->tanggal }}<br>
                            <small class="text-muted">{{ $j->shift }}</small>
                        </td>
                        <td>{{ $j->personnel->nama_lengkap }}</td>
                        <td>{{ $j->lokasi_target }}</td>
                        <td><span class="badge bg-secondary">{{ $j->status }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
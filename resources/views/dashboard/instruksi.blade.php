@extends('layouts.admin')

@section('title', 'Instruksi Personel')
@section('header-title', 'Kirim Instruksi Operasional')

@section('content')
<div class="card card-custom p-4 bg-white mb-4">
    <form action="{{ route('instruksi.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="fw-bold">Judul Instruksi</label>
            <input type="text" name="judul" class="form-control" placeholder="Misal: Siaga 1 Pengamanan Pilkada" required>
        </div>
        <div class="mb-3">
            <label class="fw-bold">Isi Pesan</label>
            <textarea name="isi_instruksi" class="form-control" rows="3" placeholder="Deskripsikan instruksi..." required></textarea>
        </div>
        <button type="submit" class="btn btn-danger"><i class="bi bi-send-fill me-2"></i>Kirim Instruksi (Broadcast)</button>
    </form>
</div>

<h5 class="fw-bold mb-3">Riwayat Instruksi</h5>
@foreach($instruksi as $ins)
<div class="alert alert-light border-start border-5 border-danger shadow-sm">
    <div class="d-flex justify-content-between">
        <h6 class="fw-bold text-danger">{{ $ins->judul }}</h6>
        <small class="text-muted">{{ $ins->created_at->diffForHumans() }}</small>
    </div>
    <p class="mb-0 text-dark">{{ $ins->isi_instruksi }}</p>
</div>
@endforeach
@endsection
@extends('layouts.admin')

@section('title', 'Data Laporan Digital')
@section('header-title', 'Data Laporan Masuk')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0"><i class="bi bi-funnel"></i> Filter & Rekap Laporan</h6>
            <a href="{{ route('laporan.pdf', request()->all()) }}" class="btn btn-sm btn-danger px-4 fw-bold shadow-sm">
                <i class="bi bi-file-earmark-pdf-fill"></i> EXPORT REKAP PDF
            </a>
        </div>
        <form action="{{ route('laporan') }}" method="GET" class="row g-2">
            <div class="col-md-3">
                <label class="small text-muted">PILIH PERSONEL</label>
                <select name="personnel_id" class="form-select form-select-sm shadow-none">
                    <option value="all">-- Semua Personel --</option>
                    @foreach($personnels as $p)
                        <option value="{{ $p->id }}" {{ request('personnel_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->nama_lengkap }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="small text-muted">DARI TANGGAL</label>
                <input type="date" name="from_date" class="form-control form-control-sm shadow-none" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-3">
                <label class="small text-muted">SAMPAI TANGGAL</label>
                <input type="date" name="to_date" class="form-control form-control-sm shadow-none" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold">FILTER DATA</button>
                <a href="{{ route('laporan') }}" class="btn btn-sm btn-light border w-100 fw-bold">RESET</a>
            </div>
        </form>
    </div>
</div>

<div class="card card-custom p-4 bg-white shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Waktu</th>
                    <th>Pelapor</th>
                    <th>Detail Kejadian</th>
                    <th>Status</th>
                    <th class="text-center">Opsi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($laporan as $item)
                <tr>
                    <td>
                        <span class="fw-bold">{{ $item->created_at->format('d/m/Y') }}</span><br>
                        <small class="text-muted">{{ $item->created_at->format('H:i') }} WIB</small>
                    </td>
                    <td>
                        <div class="fw-bold text-primary">{{ $item->personnel->nama_lengkap }}</div>
                        <small class="badge bg-light text-dark border">{{ $item->personnel->pangkat }}</small>
                    </td>
                    <td>
                        <span class="fw-bold text-dark">{{ $item->judul_kejadian }}</span><br>
                        <small class="text-muted">{{ Str::limit($item->deskripsi, 40) }}</small>
                    </td>
                    <td>
                        @if($item->status_penanganan == 'menunggu konfirmasi')
                            <span class="badge rounded-pill bg-warning text-dark px-3">Menunggu</span>
                        @elseif($item->status_penanganan == 'dikonfirmasi')
                            <span class="badge rounded-pill bg-success px-3">Dikonfirmasi</span>
                        @else
                            <span class="badge rounded-pill bg-secondary px-3">{{ $item->status_penanganan }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-sm btn-info text-white px-3" data-bs-toggle="modal" data-bs-target="#modalDetail{{ $item->id }}">Detail</button>
                            <form action="{{ route('laporan.destroy', $item->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus permanen data ini?')">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>

                <div class="modal fade" id="modalDetail{{ $item->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-dark text-white">
                                <h5 class="modal-title">Detail Laporan #{{ $item->id }}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                @if($item->foto_bukti)
                                    <img src="{{ asset('storage/' . $item->foto_bukti) }}" class="img-fluid rounded border mb-3 shadow-sm" style="max-height: 300px; width: 100%; object-fit: cover;">
                                @else
                                    <div class="py-5 bg-light rounded border text-muted">Lampiran Foto Tidak Ada</div>
                                @endif
                                <div class="text-start mt-2">
                                    <p class="mb-1"><b>Pelapor:</b> {{ $item->personnel->nama_lengkap }}</p>
                                    <p class="mb-1"><b>Judul:</b> {{ $item->judul_kejadian }}</p>
                                    <p class="mb-1"><b>Kronologi:</b> {{ $item->deskripsi }}</p>
                                    <p class="mb-0"><b>Koordinat:</b> {{ $item->latitude }}, {{ $item->longitude }}</p>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                @if($item->status_penanganan == 'menunggu konfirmasi')
                                    <a href="{{ route('laporan.status', [$item->id, 'dikonfirmasi']) }}" class="btn btn-success px-4">Terima Laporan</a>
                                @endif
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <tr><td colspan="5" class="text-center py-5 text-muted">Data tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<audio id="notifSound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>
<script>
    let lastCount = null;
    function fetchNotification() {
        fetch("{{ route('laporan.check') }}")
            .then(res => res.json())
            .then(data => {
                if (lastCount !== null && data.unread_count > lastCount) {
                    document.getElementById('notifSound').play().catch(() => {});
                    alert('🚨 PEMBERITAHUAN: Ada Laporan Masuk Baru!');
                    location.reload();
                }
                lastCount = data.unread_count;
            });
    }
    setInterval(fetchNotification, 10000);
    document.addEventListener('click', () => document.getElementById('notifSound').load(), {once:true});
</script>
@endsection
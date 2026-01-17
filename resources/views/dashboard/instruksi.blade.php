@extends('layouts.admin')

@section('title', 'Instruksi Personel')
@section('header-title', 'Kirim Instruksi & Komando')

@section('content')

<div class="card card-custom bg-danger text-white mb-4 shadow">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>SINYAL DARURAT</h5>
            <p class="mb-0 small">Tekan tombol ini untuk mengirim peringatan bahaya ke <strong>SELURUH PERSONEL</strong>.</p>
        </div>
        <form action="{{ route('instruksi.store') }}" method="POST" onsubmit="return confirm('Yakin kirim sinyal DARURAT ke semua personel?');">
            @csrf
            <input type="hidden" name="is_darurat" value="1">
            <button type="submit" class="btn btn-light text-danger fw-bold px-4 py-2">
                <i class="bi bi-broadcast me-2"></i>KIRIM SOS SEKARANG
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
    <div class="col-md-5">
        <div class="card card-custom bg-white p-4 h-100">
            <h6 class="fw-bold mb-3">Buat Instruksi Baru</h6>
            
            <form action="{{ route('instruksi.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label fw-bold small">Target Personel</label>
                    <select name="personnel_id" class="form-select">
                        <option value="all">-- Semua Personel --</option>
                        @foreach($personnels as $p)
                            <option value="{{ $p->id }}">{{ $p->nama_lengkap }} ({{ $p->pangkat }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Jenis Instruksi</label>
                    <select name="tipe_instruksi" id="tipe_instruksi" class="form-select" onchange="toggleMap()">
                        <option value="pesan">Pesan Teks Biasa</option>
                        <option value="lokasi">Menuju Lokasi (Rute)</option>
                        <option value="patroli">Perintah Patroli Khusus</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Isi Perintah</label>
                    <select name="isi_instruksi" id="pilihan_instruksi" class="form-select mb-2" onchange="fillMessage()">
                        <option value="">-- Pilih Template Instruksi --</option>
                        <option value="Lakukan penyekatan di titik koordinat berikut.">Lakukan Penyekatan (Blokade)</option>
                        <option value="Segera merapat ke lokasi TKP untuk bantuan.">Bantuan Personel (Backup)</option>
                        <option value="Laksanakan patroli dialogis di area ini.">Patroli Dialogis</option>
                        <option value="Bubarkan kerumunan di lokasi terpilih.">Pembubaran Massa</option>
                        <option value="custom">-- Ketik Manual --</option>
                    </select>
                    
                    <input type="text" name="judul" id="judul" class="form-control mb-2" placeholder="Judul Instruksi" required>
                    <textarea name="isi_instruksi" id="text_instruksi" class="form-control" rows="3" placeholder="Detail instruksi..." required></textarea>
                </div>

                <input type="hidden" name="latitude" id="lat">
                <input type="hidden" name="longitude" id="lng">

                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-send-fill me-2"></i>Kirim Instruksi
                </button>
            </form>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card card-custom bg-white p-0 h-100" id="map-wrapper" style="display:none;">
            <div class="card-header bg-light py-2">
                <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Klik peta untuk pilih tujuan.</small>
            </div>
            <div id="map-picker" style="height: 380px; width: 100%;"></div>
            <div class="p-2 text-center bg-light border-top small">
                Koordinat: <span id="coordinate-display" class="fw-bold text-primary">-</span>
            </div>
        </div>
        
        <div class="card card-custom bg-light p-5 text-center h-100 d-flex justify-content-center align-items-center" id="map-placeholder">
            <div class="text-muted">
                <i class="bi bi-map fs-1 text-secondary"></i>
                <p class="mt-2 small">Pilih Jenis Instruksi <strong>"Menuju Lokasi"</strong><br>untuk membuka peta.</p>
            </div>
        </div>
    </div>
</div>

<h5 class="fw-bold mt-4 mb-3">Riwayat Instruksi Terkirim</h5>
<div class="card card-custom bg-white p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th width="15%">Waktu</th>
                    <th width="20%">Target</th>
                    <th width="25%">Judul & Tipe</th>
                    <th>Isi / Lokasi</th>
                    <th width="10%" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($instruksi as $ins)
                <tr class="{{ $ins->tipe_instruksi == 'darurat' ? 'table-danger' : '' }}">
                    <td>
                        <span class="fw-bold">{{ $ins->created_at->format('d M') }}</span><br>
                        <small class="text-muted">{{ $ins->created_at->format('H:i') }}</small>
                    </td>
                    <td>
                        @if($ins->personnel)
                            <span class="badge bg-primary text-wrap">{{ $ins->personnel->nama_lengkap }}</span>
                        @else
                            <span class="badge bg-dark">SEMUA PERSONEL</span>
                        @endif
                    </td>
                    <td>
                        <strong class="d-block text-truncate" style="max-width: 150px;">{{ $ins->judul }}</strong>
                        <span class="badge {{ $ins->tipe_instruksi == 'darurat' ? 'bg-danger' : 'bg-secondary' }}" style="font-size: 10px;">
                            {{ strtoupper($ins->tipe_instruksi) }}
                        </span>
                    </td>
                    <td>
                        <span class="d-block text-truncate" style="max-width: 200px;">{{ $ins->isi_instruksi }}</span>
                        @if($ins->latitude)
                            <a href="https://www.google.com/maps?q={{$ins->latitude}},{{$ins->longitude}}" target="_blank" class="btn btn-xs btn-outline-primary py-0 mt-1" style="font-size: 10px;">
                                <i class="bi bi-geo-alt-fill"></i> Lihat Rute
                            </a>
                        @endif
                    </td>
                    <td class="text-center">
                        <form action="{{ route('instruksi.destroy', $ins->id) }}" method="POST" onsubmit="return confirm('Batalkan instruksi ini? Data akan dihapus.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Batalkan/Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Inisialisasi Peta
    var mapPicker = L.map('map-picker').setView([-8.0739, 111.9015], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(mapPicker);
    
    var pickedMarker = null;

    // Event Klik Peta
    mapPicker.on('click', function(e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;

        // Isi input hidden
        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;
        
        // Tampilkan teks koordinat
        document.getElementById('coordinate-display').innerText = lat.toFixed(6) + ", " + lng.toFixed(6);

        // Pasang/Pindah Marker
        if (pickedMarker) {
            pickedMarker.setLatLng(e.latlng);
        } else {
            pickedMarker = L.marker(e.latlng, {draggable: true}).addTo(mapPicker);
            // Update jika marker digeser
            pickedMarker.on('dragend', function(event){
                var position = event.target.getLatLng();
                document.getElementById('lat').value = position.lat;
                document.getElementById('lng').value = position.lng;
                document.getElementById('coordinate-display').innerText = position.lat.toFixed(6) + ", " + position.lng.toFixed(6);
            });
        }
    });

    // Toggle Tampilan Peta
    function toggleMap() {
        var tipe = document.getElementById('tipe_instruksi').value;
        var mapWrapper = document.getElementById('map-wrapper');
        var mapPlaceholder = document.getElementById('map-placeholder');

        if (tipe === 'lokasi') {
            mapWrapper.style.display = 'block';
            mapPlaceholder.style.display = 'none';
            setTimeout(function(){ mapPicker.invalidateSize(); }, 300); // Agar peta render sempurna
        } else {
            mapWrapper.style.display = 'none';
            mapPlaceholder.style.display = 'flex';
            document.getElementById('lat').value = '';
            document.getElementById('lng').value = '';
        }
    }

    // Auto-fill Pesan
    function fillMessage() {
        var select = document.getElementById('pilihan_instruksi');
        var text = document.getElementById('text_instruksi');
        var judul = document.getElementById('judul');
        
        if (select.value === 'custom') {
            text.value = '';
            judul.value = '';
        } else if (select.value !== '') {
            text.value = select.value;
            judul.value = select.options[select.selectedIndex].text;
        }
    }
</script>
@endpush
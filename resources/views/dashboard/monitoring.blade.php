@extends('layouts.admin')

@section('title', 'GPS Realtime')
@section('header-title', 'Peta Sebaran Personel')

@section('content')
<div style="background: white; padding: 0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); height: calc(100vh - 120px); position: relative;">
    <div id="map" style="height: 100%; width: 100%;"></div>

    <div style="position: absolute; top: 20px; right: 20px; width: 300px; background: rgba(255, 255, 255, 0.95); padding: 15px; border-radius: 10px; z-index: 999; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
        <h6 class="fw-bold mb-3"><i class="bi bi-people-fill me-2"></i>Status Personel</h6>
        <div id="personnel-list-container">
            <div class="text-center text-muted small">Memuat data...</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var map = L.map('map').setView([-8.0739, 111.9015], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OSM' }).addTo(map);

    var policeIcon = L.divIcon({
        className: 'custom-div-icon',
        html: "<div style='background-color:#0F172A; width:30px; height:30px; border-radius:50%; border:2px solid #fff; display:flex; justify-content:center; align-items:center; box-shadow:0 3px 6px rgba(0,0,0,0.3);'><i class='bi bi-person-fill' style='color:#FFD700; font-size:16px;'></i></div>",
        iconSize: [30, 30]
    });

    var markers = {};

    function updateMap() {
        fetch('/get-locations')
            .then(response => response.json())
            .then(data => {
                var listHtml = '';
                data.forEach(person => {
                    // Logic warna status
                    var statusClass = 'bg-success'; 
                    if (person.status_aktif === 'patroli') statusClass = 'bg-primary'; // Biru untuk patroli
                    else if (person.status_aktif === 'darurat') statusClass = 'bg-danger';

                    // List HTML
                    listHtml += `
                        <div style="padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;" onclick="map.flyTo([${person.latitude}, ${person.longitude}], 16)">
                            <div>
                                <div class="fw-bold" style="font-size:13px;">${person.nama_lengkap}</div>
                                <div class="text-muted" style="font-size:11px;">${person.pangkat}</div>
                            </div>
                            <span class="badge rounded-pill ${statusClass}" style="font-size:10px;">${person.status_aktif}</span>
                        </div>
                    `;

                    // Marker
                    if (markers[person.id]) {
                        markers[person.id].setLatLng([person.latitude, person.longitude]);
                    } else {
                        markers[person.id] = L.marker([person.latitude, person.longitude], {icon: policeIcon}).addTo(map);
                    }
                });

                document.getElementById('personnel-list-container').innerHTML = data.length ? listHtml : '<div class="text-center text-muted">Tidak ada aktif</div>';
            });
    }

    setInterval(updateMap, 3000);
    updateMap();
</script>
@endpush
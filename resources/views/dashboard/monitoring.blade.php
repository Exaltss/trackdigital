@extends('layouts.admin')

@section('title', 'GPS Realtime')
@section('header-title', 'Peta Sebaran Personel')

@section('content')
<style>
    /* Reset Style Leaflet Popup */
    .leaflet-popup-content-wrapper {
        border-radius: 8px !important;
        padding: 0 !important;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3) !important;
    }
    .leaflet-popup-content {
        margin: 0 !important;
        width: 300px !important;
    }
    .leaflet-popup-close-button {
        top: 8px !important;
        right: 8px !important;
        color: white !important;
        text-shadow: 0 0 2px rgba(0,0,0,0.5);
        font-size: 18px !important;
    }
    
    /* Layout Kartu */
    .gmaps-card {
        font-family: 'Roboto', Arial, sans-serif;
        background: white;
    }

    /* Header Default */
    .gmaps-header {
        background-color: #4285F4;
        color: white;
        padding: 12px 15px;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
    }

    /* Body Teks */
    .gmaps-body { padding: 15px; }
    
    .gmaps-title {
        font-size: 16px;
        font-weight: 600;
        color: #202124;
        margin-bottom: 5px;
        line-height: 1.3;
    }

    .gmaps-subtitle {
        font-size: 13px;
        color: #5f6368;
        margin-bottom: 10px;
    }
    
    .gmaps-label {
        font-size: 11px;
        font-weight: bold;
        color: #1A73E8;
        text-transform: uppercase;
        margin-bottom: 2px;
        margin-top: 8px;
    }

    /* Koordinat Box */
    .coord-box {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        padding: 6px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 12px;
        color: #d63384;
        text-align: center;
        margin-top: 5px;
    }

    /* Footer Tombol */
    .gmaps-footer {
        border-top: 1px solid #E8EAED;
        display: flex;
    }
    .gmaps-btn {
        flex: 1;
        text-align: center;
        padding: 12px 0;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        color: #1A73E8;
        background: white;
        transition: 0.2s;
    }
    .gmaps-btn:hover { background: #F1F3F4; color: #174EA6; }
    .gmaps-btn:first-child { border-right: 1px solid #E8EAED; }
</style>

<div class="card card-custom p-0 overflow-hidden" style="height: calc(100vh - 120px); position: relative;">
    <div id="map" style="height: 100%; width: 100%;"></div>

    <div style="position: absolute; top: 10px; right: 10px; width: 300px; background: rgba(255, 255, 255, 0.95); padding: 15px; border-radius: 10px; z-index: 999; box-shadow: 0 5px 15px rgba(0,0,0,0.2); max-height: 80%; overflow-y: auto;">
        <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">
            <i class="bi bi-people-fill me-2 text-primary"></i>Status Personel
        </h6>
        <div id="personnel-list-container">
            <div class="text-center text-muted small py-3">
                <div class="spinner-border spinner-border-sm text-secondary mb-2" role="status"></div>
                <div>Menghubungkan GPS...</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // 1. LAYER PETA
    var googleStreets = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',{
        maxZoom: 21, subdomains:['mt0','mt1','mt2','mt3'], attribution: '© Google'
    });
    var googleHybrid = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}',{
        maxZoom: 21, subdomains:['mt0','mt1','mt2','mt3'], attribution: '© Google'
    });

    // 2. SETUP MAP
    var map = L.map('map', {
        center: [-8.0739, 111.9015], 
        zoom: 16,
        layers: [googleStreets],
        zoomControl: false 
    });
    L.control.zoom({ position: 'topleft' }).addTo(map);
    L.control.layers({ "Peta Jalan": googleStreets, "Satelit": googleHybrid }).addTo(map);

    // 3. LOGIKA POPUP LOKASI (NON-PERSONEL)
    var detailPopup = L.popup();

    map.on('click', function(e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;
        
        detailPopup.setLatLng(e.latlng).setContent('<div class="p-4 text-center">Loading info...</div>').openOn(map);

        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
        .then(res => res.json())
        .then(data => {
            var name = data.address.amenity || data.address.building || "Lokasi Terpilih";
            var fullAddress = data.display_name;
            var mapsUrl = `http://maps.google.com/maps?q=&layer=c&cbll=${lat},${lng}`;
            var routeUrl = `http://maps.google.com/maps?q=${lat},${lng}`;

            var content = `
            <div class="gmaps-card">
                <div class="gmaps-header">
                    <i class="bi bi-geo-alt-fill me-2"></i> Info Detail Lokasi
                </div>
                <div class="gmaps-body">
                    <div class="gmaps-title">${name}</div>
                    <div style="font-size:13px; color:#3C4043; margin-bottom:5px;">${fullAddress}</div>
                    <div class="gmaps-label">Titik Koordinat:</div>
                    <div class="coord-box">${lat.toFixed(6)}, ${lng.toFixed(6)}</div>
                </div>
                <div class="gmaps-footer">
                    <a href="${mapsUrl}" target="_blank" class="gmaps-btn text-primary"><i class="bi bi-person-bounding-box me-1"></i> Street View</a>
                    <a href="${routeUrl}" target="_blank" class="gmaps-btn text-success"><i class="bi bi-cursor-fill me-1"></i> Rute</a>
                </div>
            </div>`;
            detailPopup.setContent(content);
        });
    });

    // 4. LOGIKA MARKER PERSONEL
    var policeIcon = L.divIcon({
        className: 'custom-div-icon',
        html: "<div style='background-color:#0F172A; width:35px; height:35px; border-radius:50%; border:2px solid #fff; display:flex; justify-content:center; align-items:center; box-shadow:0 3px 6px rgba(0,0,0,0.4);'><i class='bi bi-person-fill' style='color:#FFD700; font-size:20px;'></i></div>",
        iconSize: [35, 35],
        iconAnchor: [17, 17],
        popupAnchor: [0, -20]
    });

    var markers = {};

    function updateMap() {
        fetch('/get-locations')
            .then(response => response.json())
            .then(data => {
                var listHtml = '';
                
                data.forEach(person => {
                    // --- LOGIKA WARNA HEADER ---
                    // Default Values (Untuk yang sekadar 'Online')
                    var statusBadge = '<span class="badge bg-secondary">AKTIF</span>'; // Neutral
                    var headerColor = '#4285F4'; // Default Blue Google (Standard)
                    var statusIcon = 'bi-person-fill'; // Neutral Icon

                    // Cek Status Khusus
                    if (person.status_aktif === 'patroli') {
                        statusBadge = '<span class="badge bg-primary">SEDANG PATROLI</span>';
                        headerColor = '#0d6efd'; // Biru Tua (Patroli)
                        statusIcon = 'bi-shield-fill';
                    }
                    else if (person.status_aktif === 'siaga') {
                        statusBadge = '<span class="badge bg-warning text-dark">BERSIAGA</span>';
                        headerColor = '#fd7e14'; // ORANYE (Siaga) - Sesuai permintaan
                        statusIcon = 'bi-pause-circle-fill';
                    }
                    else if (person.status_aktif === 'darurat') {
                        statusBadge = '<span class="badge bg-danger">KONDISI DARURAT!</span>';
                        headerColor = '#dc3545'; // Merah (Darurat)
                        statusIcon = 'bi-exclamation-triangle-fill';
                    }
                    // Status 'Online' (hijau) SUDAHDIHAPUS. 
                    // Jika status != patroli/siaga/darurat, akan pakai Default Values di atas.

                    // Sidebar Item
                    listHtml += `
                        <div class="card mb-2 border-0 shadow-sm" style="cursor: pointer; transition:0.2s;" onclick="flyToPersonnel(${person.latitude}, ${person.longitude})">
                            <div class="card-body p-2 d-flex align-items-center">
                                <div class="bg-light rounded-circle p-2 me-2">
                                    <i class="bi bi-person-circle fs-4 text-secondary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark" style="font-size:13px;">${person.nama_lengkap}</div>
                                    <div class="text-muted" style="font-size:11px;">${person.pangkat}</div>
                                </div>
                                <div>${statusBadge}</div>
                            </div>
                        </div>
                    `;

                    // Popup Content
                    var mapsUrl = `https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=$${person.latitude},${person.longitude}`;
                    var routeUrl = `http://maps.google.com/maps?q=$${person.latitude},${person.longitude}`;

                    var popupContent = `
                        <div class="gmaps-card">
                            <div class="gmaps-header" style="background-color: ${headerColor};">
                                <i class="bi ${statusIcon} me-2"></i> Info Personel
                            </div>
                            <div class="gmaps-body">
                                <div class="gmaps-title">${person.nama_lengkap}</div>
                                <div class="gmaps-subtitle">${person.pangkat} • ${person.status_aktif.toUpperCase()}</div>
                                
                                <div class="gmaps-label">Status Terkini:</div>
                                <div class="mb-2">${statusBadge}</div>

                                <div class="gmaps-label">Lokasi Saat Ini:</div>
                                <div class="coord-box">${person.latitude}, ${person.longitude}</div>
                            </div>
                            <div class="gmaps-footer">
                                <a href="${mapsUrl}" target="_blank" class="gmaps-btn text-primary">
                                    <i class="bi bi-person-bounding-box me-1"></i> Street View
                                </a>
                                <a href="${routeUrl}" target="_blank" class="gmaps-btn text-success">
                                    <i class="bi bi-cursor-fill me-1"></i> Rute Menuju
                                </a>
                            </div>
                        </div>
                    `;

                    if (markers[person.id]) {
                        markers[person.id].setLatLng([person.latitude, person.longitude]);
                        markers[person.id].setPopupContent(popupContent);
                    } else {
                        var newMarker = L.marker([person.latitude, person.longitude], {icon: policeIcon}).addTo(map);
                        newMarker.bindPopup(popupContent);
                        markers[person.id] = newMarker;
                    }
                });

                var container = document.getElementById('personnel-list-container');
                if(data.length > 0) {
                    container.innerHTML = listHtml;
                } else {
                    container.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-person-x fs-1"></i><p class="mb-0 mt-2">Tidak ada personel aktif.</p></div>';
                }
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    function flyToPersonnel(lat, lng) {
        map.flyTo([lat, lng], 18, { animate: true, duration: 1.5 });
    }

    setInterval(updateMap, 3000);
    updateMap();
</script>
@endpush
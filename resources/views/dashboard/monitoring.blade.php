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
        width: 320px !important; 
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

    .gmaps-header {
        color: white;
        padding: 12px 15px;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
    }

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

    @keyframes pulse-red {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }
    .pulse-emergency {
        animation: pulse-red 2s infinite;
        border: 3px solid white !important;
    }

    .btn-stop-focus {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
        border-radius: 30px;
        padding: 10px 25px;
        font-weight: bold;
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
        display: none;
        animation: slideUp 0.3s ease-out;
    }
    @keyframes slideUp {
        from { bottom: -50px; opacity: 0; }
        to { bottom: 30px; opacity: 1; }
    }
</style>

<div class="card card-custom p-0 overflow-hidden" style="height: calc(100vh - 120px); position: relative;">
    <div id="map" style="height: 100%; width: 100%;"></div>

    <button id="btn-stop-focus" class="btn btn-danger btn-stop-focus" onclick="stopFocus()">
        <i class="bi bi-x-circle-fill me-2"></i>Hentikan Fokus
    </button>

    <div style="position: absolute; top: 10px; right: 10px; width: 320px; background: rgba(255, 255, 255, 0.95); padding: 15px; border-radius: 10px; z-index: 999; box-shadow: 0 5px 15px rgba(0,0,0,0.2); max-height: 85%; overflow-y: auto;">
        <h6 class="fw-bold mb-3 text-dark border-bottom pb-2 d-flex justify-content-between">
            <span><i class="bi bi-people-fill me-2 text-primary"></i>Status Personel</span>
            <span id="sync-status" class="badge bg-success" style="font-size: 10px;">LIVE</span>
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
        zoom: 14,
        layers: [googleStreets],
        zoomControl: false 
    });
    L.control.zoom({ position: 'topleft' }).addTo(map);
    L.control.layers({ "Peta Jalan": googleStreets, "Satelit": googleHybrid }).addTo(map);

    // 3. VARIABEL MARKER & JALUR
    var markers = {};
    var polylines = {}; 
    var instructionPolylines = {}; 
    var focusedPersonnelId = null; 

    var checkpointLayer = L.layerGroup().addTo(map);
    var checkpointMarkers = {}; 
    
    var urlParams = new URLSearchParams(window.location.search);
    var targetCpId = urlParams.get('cp_id'); 
    var hasFocusedCp = false; 

    function updateMap() {
        fetch('{{ url("/get-locations") }}')
            .then(response => response.json())
            .then(data => {
                var listHtml = '';
                var activeIds = [];
                
                data.forEach(person => {
                    activeIds.push(person.id);

                    var statusAktif = person.status_aktif ? person.status_aktif.toLowerCase() : 'online';
                    var statusLabel = person.status_aktif ? person.status_aktif.toUpperCase() : 'ONLINE';
                    var statusBadge = '<span class="badge bg-secondary">ONLINE</span>';
                    var headerColor = '#4285F4'; 
                    var statusIcon = 'bi-person-fill';
                    var markerClass = '';

                    if (statusAktif === 'patroli') {
                        statusBadge = '<span class="badge bg-primary">SEDANG PATROLI</span>';
                        headerColor = '#0d6efd'; 
                        statusIcon = 'bi-shield-fill';
                    }
                    else if (statusAktif === 'bersiaga') {
                        statusBadge = '<span class="badge bg-warning text-dark">BERSIAGA</span>';
                        headerColor = '#fd7e14'; 
                        statusIcon = 'bi-pause-circle-fill';
                    }
                    else if (statusAktif === 'darurat') {
                        statusBadge = '<span class="badge bg-danger">KONDISI DARURAT!</span>';
                        headerColor = '#dc3545'; 
                        statusIcon = 'bi-exclamation-triangle-fill';
                        markerClass = 'pulse-emergency'; 
                    }

                    var pLat = parseFloat(person.latitude);
                    var pLng = parseFloat(person.longitude);

                    if (focusedPersonnelId === person.id && !isNaN(pLat) && !isNaN(pLng)) {
                        map.panTo([pLat, pLng], { animate: true, duration: 1.0 });
                    }

                    // --- LOGIKA JALUR PATROLI (BIRU SOLID) ---
                    var waypoints = [];
                    if (!isNaN(pLng) && !isNaN(pLat)) { waypoints.push([pLng, pLat]); }
                    if (person.schedules && person.schedules.length > 0) {
                        person.schedules.forEach(jadwal => {
                            if (jadwal.latitude && jadwal.longitude) {
                                var jLat = parseFloat(jadwal.latitude);
                                var jLng = parseFloat(jadwal.longitude);
                                if (!isNaN(jLng) && !isNaN(jLat)) { waypoints.push([jLng, jLat]); }
                            }
                        });
                    }

                    if (waypoints.length > 1) {
                        var routeSig = waypoints.map(w => w[0].toFixed(4) + ',' + w[1].toFixed(4)).join(';');
                        if (!polylines[person.id] || polylines[person.id].routeSig !== routeSig) {
                            var osrmUrl = `https://router.project-osrm.org/route/v1/driving/${routeSig}?overview=full&geometries=geojson`;
                            fetch(osrmUrl)
                                .then(res => res.json())
                                .then(routeData => {
                                    if (routeData.code === 'Ok' && routeData.routes.length > 0) {
                                        var latLngs = routeData.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                                        if (polylines[person.id] && polylines[person.id].layer) {
                                            map.removeLayer(polylines[person.id].layer);
                                        }
                                        // JALUR PATROLI WARNA BIRU (#007bff)
                                        var routeLine = L.polyline(latLngs, { color: '#007bff', weight: 5, opacity: 0.7, lineJoin: 'round' }).addTo(map);
                                        polylines[person.id] = { layer: routeLine, routeSig: routeSig };
                                    }
                                });
                        }
                    } else {
                        if (polylines[person.id] && polylines[person.id].layer) { map.removeLayer(polylines[person.id].layer); delete polylines[person.id]; }
                    }

                    // --- LOGIKA JALUR INSTRUKSI (MERAH PUTUS-PUTUS) ---
                    if (person.latest_instruction && person.latest_instruction.latitude && person.latest_instruction.longitude) {
                        var iLat = parseFloat(person.latest_instruction.latitude);
                        var iLng = parseFloat(person.latest_instruction.longitude);
                        
                        var instrWaypoints = [[pLng, pLat], [iLng, iLat]];
                        var instrSig = instrWaypoints.map(w => w[0].toFixed(4) + ',' + w[1].toFixed(4)).join(';');

                        if (!instructionPolylines[person.id] || instructionPolylines[person.id].routeSig !== instrSig) {
                            var osrmInstrUrl = `https://router.project-osrm.org/route/v1/driving/${instrSig}?overview=full&geometries=geojson`;
                            
                            fetch(osrmInstrUrl)
                                .then(res => res.json())
                                .then(routeData => {
                                    if (routeData.code === 'Ok' && routeData.routes.length > 0) {
                                        var latLngs = routeData.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                                        if (instructionPolylines[person.id] && instructionPolylines[person.id].layer) {
                                            map.removeLayer(instructionPolylines[person.id].layer);
                                        }
                                        // JALUR INSTRUKSI WARNA MERAH (#dc3545) & PUTUS-PUTUS
                                        var instrLine = L.polyline(latLngs, { 
                                            color: '#dc3545', 
                                            weight: 4, 
                                            opacity: 0.9, 
                                            dashArray: '5, 10', 
                                            lineJoin: 'round' 
                                        }).addTo(map);
                                        instructionPolylines[person.id] = { layer: instrLine, routeSig: instrSig };
                                    }
                                });
                        }
                    } else {
                        if (instructionPolylines[person.id] && instructionPolylines[person.id].layer) {
                            map.removeLayer(instructionPolylines[person.id].layer);
                            delete instructionPolylines[person.id];
                        }
                    }

                    // --- MARKER & SIDEBAR ---
                    var focusHighlight = (focusedPersonnelId === person.id) ? 'border: 2px solid #1A73E8; background-color: #f1f8ff;' : 'border: 0;';
                    listHtml += `
                        <div class="card mb-2 shadow-sm personnel-card" style="cursor: pointer; ${focusHighlight}" onclick="flyToPersonnel(${pLat}, ${pLng}, ${person.id})">
                            <div class="card-body p-2 d-flex align-items-center">
                                <div class="bg-light rounded-circle p-2 me-2"><i class="bi bi-person-circle fs-4 text-secondary"></i></div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark" style="font-size:13px;">${person.nama_lengkap}</div>
                                    <div class="text-muted" style="font-size:11px;">${person.pangkat}</div>
                                </div>
                                <div>${statusBadge}</div>
                            </div>
                        </div>
                    `;

                    var googleMapsUrl = `https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=${pLat},${pLng}`;
                    var popupContent = `
                        <div class="gmaps-card">
                            <div class="gmaps-header" style="background-color: ${headerColor};">
                                <i class="bi ${statusIcon} me-2"></i> Info Personel
                            </div>
                            <div class="gmaps-body">
                                <div class="gmaps-title">${person.nama_lengkap}</div>
                                <div class="gmaps-subtitle">${person.pangkat} • ${statusLabel}</div>
                                <div class="gmaps-label">Status:</div><div class="mb-2">${statusBadge}</div>
                                <div class="gmaps-label">Koordinat:</div><div class="coord-box">${pLat}, ${pLng}</div>
                            </div>
                            <div class="gmaps-footer">
                                <a href="${googleMapsUrl}" target="_blank" class="gmaps-btn text-primary"><i class="bi bi-eye-fill me-1"></i> Street View</a>
                                <a href="javascript:void(0)" onclick="flyToPersonnel(${pLat}, ${pLng}, ${person.id})" class="gmaps-btn text-success"><i class="bi bi-geo-fill me-1"></i> Fokus</a>
                            </div>
                        </div>`;

                    var policeIcon = L.divIcon({
                        className: 'custom-div-icon',
                        html: `<div class="${markerClass}" style='background-color:#0F172A; width:35px; height:35px; border-radius:50%; border:2px solid #fff; display:flex; justify-content:center; align-items:center; box-shadow:0 3px 6px rgba(0,0,0,0.4);'><i class='bi bi-person-fill' style='color:#FFD700; font-size:20px;'></i></div>`,
                        iconSize: [35, 35],
                        iconAnchor: [17, 17],
                        popupAnchor: [0, -20]
                    });

                    if (markers[person.id]) {
                        markers[person.id].setLatLng([pLat, pLng]);
                        if(!markers[person.id].isPopupOpen()){ markers[person.id].setPopupContent(popupContent); }
                        markers[person.id].setIcon(policeIcon);
                    } else {
                        var newMarker = L.marker([pLat, pLng], {icon: policeIcon}).addTo(map);
                        newMarker.bindPopup(popupContent);
                        markers[person.id] = newMarker;
                    }
                });

                Object.keys(markers).forEach(id => {
                    var parsedId = parseInt(id);
                    if (!activeIds.includes(parsedId)) {
                        map.removeLayer(markers[id]); delete markers[id];
                        if (polylines[id]) { map.removeLayer(polylines[id].layer); delete polylines[id]; }
                        if (instructionPolylines[id]) { map.removeLayer(instructionPolylines[id].layer); delete instructionPolylines[id]; }
                        if (focusedPersonnelId === parsedId) stopFocus();
                    }
                });

                document.getElementById('personnel-list-container').innerHTML = (activeIds.length > 0) ? listHtml : 
                    '<div class="text-center text-muted py-4"><i class="bi bi-person-x fs-1"></i><p class="mb-0 mt-2">Tidak ada personel aktif.</p></div>';
                document.getElementById('sync-status').className = 'badge bg-success';
                document.getElementById('sync-status').innerText = 'LIVE: ' + new Date().toLocaleTimeString();
            })
            .catch(error => {
                document.getElementById('sync-status').className = 'badge bg-danger';
                document.getElementById('sync-status').innerText = 'OFFLINE';
            });
    }

    function fetchCheckpoints() {
        fetch('{{ url("/get-checkpoints-json") }}') 
            .then(res => res.json())
            .then(data => {
                var reports = data.data ? data.data : data;
                var activeCpIds = [];
                var baseUrlStorage = '{{ asset("storage") }}';

                if (Array.isArray(reports)) {
                    reports.forEach(laporan => {
                        if (laporan.tipe_laporan === 'checkpoint' && laporan.latitude && laporan.longitude) {
                            activeCpIds.push(laporan.id);
                            var pLat = parseFloat(laporan.latitude);
                            var pLng = parseFloat(laporan.longitude);
                            if(isNaN(pLat) || isNaN(pLng)) return;

                            var waktu = new Date(laporan.created_at).toLocaleString('id-ID');
                            var personelNama = laporan.personnel ? laporan.personnel.nama_lengkap : 'Personel';
                            var judul = laporan.judul_laporan || laporan.judul_kejadian || 'Titik Checkpoint';
                            var isi = laporan.isi_laporan || laporan.deskripsi || '-';
                            var isiLower = isi.toLowerCase();

                            var markerColor = '#0d6efd';
                            var textColor = '#ffffff';
                            if (isiLower.includes('tingkat: aman')) markerColor = '#0d6efd';
                            else if (isiLower.includes('tingkat: rendah')) markerColor = '#28a745';
                            else if (isiLower.includes('tingkat: sedang')) { markerColor = '#ffc107'; textColor = '#212529'; }
                            else if (isiLower.includes('tingkat: tinggi')) markerColor = '#dc3545';

                            var fotoHtml = laporan.foto_bukti ? `
                                <div style="margin-bottom: 12px; text-align: center;">
                                    <a href="${baseUrlStorage}/${laporan.foto_bukti}" target="_blank">
                                        <img src="${baseUrlStorage}/${laporan.foto_bukti}" style="width: 100%; max-height: 180px; object-fit: cover; border-radius: 6px;">
                                    </a>
                                </div>` : '';

                            var popupHTML = `
                                <div style="min-width: 250px; font-family: 'Roboto', sans-serif;">
                                    <div style="background-color: ${markerColor}; color: ${textColor}; padding: 8px 10px; font-weight: bold; border-radius: 5px 5px 0 0;">
                                        <i class="bi bi-geo-alt-fill"></i> Data Checkpoint
                                    </div>
                                    <div style="padding: 12px; background: white; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px;">
                                        ${fotoHtml}
                                        <div style="font-size:15px; font-weight:bold; color:#333;">${judul}</div>
                                        <div style="font-size:12px; color:#666; margin-bottom:10px;">Oleh: <b>${personelNama}</b></div>
                                        <div style="font-size:12px; background:#f8f9fa; padding:8px; border-radius:5px; margin-bottom:10px; white-space: pre-wrap;">${isi}</div>
                                        <div style="font-size:11px; color:#888;">
                                            <i class="bi bi-clock"></i> ${waktu}<br>
                                            <i class="bi bi-compass"></i> ${pLat}, ${pLng}
                                        </div>
                                    </div>
                                </div>`;

                            var cpIcon = L.divIcon({
                                className: 'custom-cp-icon',
                                html: `<div style='background-color:${markerColor}; width:28px; height:28px; border-radius:50%; border:2px solid #fff; display:flex; justify-content:center; align-items:center;'><i class='bi bi-pin-map-fill' style='color:${textColor}; font-size:16px;'></i></div>`,
                                iconSize: [28, 28], iconAnchor: [14, 28], popupAnchor: [0, -25]
                            });

                            if (checkpointMarkers[laporan.id]) {
                                checkpointMarkers[laporan.id].setLatLng([pLat, pLng]);
                                checkpointMarkers[laporan.id].setPopupContent(popupHTML);
                                checkpointMarkers[laporan.id].setIcon(cpIcon);
                            } else {
                                checkpointMarkers[laporan.id] = L.marker([pLat, pLng], {icon: cpIcon}).bindPopup(popupHTML).addTo(checkpointLayer);
                            }

                            if (targetCpId && parseInt(targetCpId) === parseInt(laporan.id) && !hasFocusedCp) {
                                hasFocusedCp = true; 
                                setTimeout(() => {
                                    map.flyTo([pLat, pLng], 18, { animate: true, duration: 1.5 });
                                    setTimeout(() => { if(checkpointMarkers[laporan.id]) checkpointMarkers[laporan.id].openPopup(); }, 1600);
                                }, 500); 
                            }
                        }
                    });

                    Object.keys(checkpointMarkers).forEach(id => {
                        if (!activeCpIds.includes(parseInt(id))) { checkpointLayer.removeLayer(checkpointMarkers[id]); delete checkpointMarkers[id]; }
                    });
                }
            });
    }

    function flyToPersonnel(lat, lng, id) {
        if (!isNaN(lat) && !isNaN(lng)) {
            map.flyTo([lat, lng], 18, { animate: true, duration: 1.5 });
            if (id) {
                focusedPersonnelId = id;
                document.getElementById('btn-stop-focus').style.display = 'block';
                if(markers[id]) setTimeout(() => markers[id].openPopup(), 1600);
            }
        }
    }

    function stopFocus() {
        focusedPersonnelId = null; 
        document.getElementById('btn-stop-focus').style.display = 'none'; 
        map.setZoom(14, {animate: true});
        if (targetCpId) {
            window.history.pushState({}, document.title, window.location.pathname);
            targetCpId = null;
        }
        updateMap();
    }

    setInterval(updateMap, 1500);
    updateMap();
    fetchCheckpoints();
    setInterval(fetchCheckpoints, 5000);
</script>
@endpush
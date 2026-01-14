<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Monitoring') - Polres Tulungagung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; overflow-x: hidden; }
        .sidebar { width: 280px; height: 100vh; background-color: #0F172A; color: white; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; padding: 20px; z-index: 1000; }
        .sidebar-header { display: flex; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #334155; }
        .btn-instruksi { background-color: #EF4444; color: white; border: none; width: 100%; padding: 12px; border-radius: 8px; font-weight: 600; text-align: left; display: flex; align-items: center; margin-bottom: 20px; text-decoration: none; }
        .btn-instruksi:hover { background-color: #dc2626; color: white; }
        .menu-label { font-size: 11px; text-transform: uppercase; color: #64748b; margin-bottom: 10px; font-weight: 600; }
        .nav-link { color: #cbd5e1; padding: 12px 15px; border-radius: 8px; margin-bottom: 5px; display: flex; align-items: center; font-size: 14px; transition: all 0.3s; }
        .nav-link:hover, .nav-link.active { background-color: #1e293b; color: #38bdf8; font-weight: 600; }
        .main-content { margin-left: 280px; padding: 20px; width: calc(100% - 280px); }
        .top-bar { background: white; padding: 15px 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <div style="width: 40px; height: 40px; background: #FFD700; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                <i class="bi bi-shield-fill text-dark"></i>
            </div>
            <div><h5>Admin Monitoring</h5><span>Polres Tulungagung</span></div>
        </div>
        <a href="{{ route('instruksi') }}" class="btn-instruksi"><i class="bi bi-megaphone-fill me-2"></i>Instruksi Personel</a>
        
        <div class="menu-label">Monitoring</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
            <i class="bi bi-geo-alt-fill me-3"></i>GPS Realtime
        </a>

        <div class="menu-label mt-3">Digital Report</div>
        <a href="{{ route('laporan') }}" class="nav-link {{ request()->is('laporan-digital') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text-fill me-3"></i>Data Laporan
        </a>
        <a href="{{ route('checkpoint') }}" class="nav-link {{ request()->is('checkpoint-log') ? 'active' : '' }}">
            <i class="bi bi-geo-fill me-3"></i>Checkpoint Log
        </a>
        <a href="{{ route('jadwal') }}" class="nav-link {{ request()->is('jadwal-personel') ? 'active' : '' }}">
            <i class="bi bi-calendar-check-fill me-3"></i>Jadwal Personel
        </a>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h4 class="m-0 fw-bold text-dark">@yield('header-title', 'Dashboard')</h4>
            <div class="d-flex align-items-center">
                <div class="me-3 text-end"><span class="d-block fw-bold">Administrator</span><span class="text-muted small">Online</span></div>
                <div style="width: 35px; height: 35px; background: #ccc; border-radius: 50%;"></div>
            </div>
        </div>
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
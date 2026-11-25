<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - SIKAS-RT Smart IoT</title>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-body: #0f172a;
            --bg-sidebar: #1e293b;
            --bg-card: #1e293b;
            --bg-card-hover: #334155;
            --border-color: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --secondary: #0ea5e9;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --cyan: #06b6d4;
            --shadow-glass: 0 20px 25px -5px rgba(0, 0, 0, 0.4), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
            --gradient-accent: linear-gradient(135deg, #6366f1 0%, #a855f7 50%, #ec4899 100%);
            --gradient-iot: linear-gradient(135deg, #06b6d4 0%, #3b82f6 50%, #8b5cf6 100%);
            --gradient-emerald: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --gradient-sky: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            --gradient-amber: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --gradient-danger: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            transition: all 0.3s ease;
        }

        .sidebar-brand {
            padding: 24px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--gradient-iot);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(6, 182, 212, 0.4);
            animation: pulse-glow 3s infinite alternate;
        }

        @keyframes pulse-glow {
            0% { box-shadow: 0 0 10px rgba(6, 182, 212, 0.4); }
            100% { box-shadow: 0 0 20px rgba(99, 102, 241, 0.6); }
        }

        .brand-text h2 {
            font-size: 18px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .brand-text h2 span {
            background: var(--gradient-iot);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-text p {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .sidebar-nav {
            padding: 20px 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1;
            overflow-y: auto;
        }

        .nav-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            padding: 8px 12px 4px;
            font-weight: 700;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
            position: relative;
        }

        .nav-item i {
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
        }

        .nav-item.active {
            border-left: 3px solid var(--primary);
        }

        .nav-item.nav-iot {
            background: rgba(6, 182, 212, 0.08);
            border: 1px dashed rgba(6, 182, 212, 0.3);
            color: #38bdf8;
        }

        .nav-item.nav-iot:hover, .nav-item.nav-iot.active {
            background: rgba(6, 182, 212, 0.2);
            color: #38bdf8;
            border-color: #38bdf8;
        }

        .sidebar-user {
            padding: 16px 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(15, 23, 42, 0.5);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            color: #fff;
        }

        .user-details h4 {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
        }

        .user-role-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .role-superadmin { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }
        .role-bendahara { background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.3); }
        .role-rt { background: rgba(14, 165, 233, 0.2); color: #7dd3fc; border: 1px solid rgba(14, 165, 233, 0.3); }
        .role-warga { background: rgba(245, 158, 11, 0.2); color: #fde047; border: 1px solid rgba(245, 158, 11, 0.3); }

        /* Main Content Styling */
        .main-content {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Topbar Styling */
        .topbar {
            height: 70px;
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .page-title h1 {
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .iot-online-badge {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
        }

        .dot-pulse {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            animation: pulse-ring 1.5s infinite;
        }

        @keyframes pulse-ring {
            0% { transform: scale(0.9); opacity: 0.8; }
            50% { transform: scale(1.3); opacity: 1; }
            100% { transform: scale(0.9); opacity: 0.8; }
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .quick-role-bar {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(15, 23, 42, 0.85);
            padding: 6px 12px;
            border-radius: 30px;
            border: 1px solid var(--border-color);
        }

        .quick-role-label {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 600;
            margin-right: 4px;
        }

        .btn-role-chip {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-role-chip:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }

        .btn-panic-quick {
            background: var(--gradient-danger);
            color: #fff;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
            transition: transform 0.2s;
        }

        .btn-panic-quick:hover {
            transform: scale(1.03);
        }

        .content-body {
            padding: 30px;
            flex: 1;
        }

        /* Emergency Warning Strip */
        .emergency-banner {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.95), rgba(185, 28, 28, 0.95));
            border: 2px solid #f87171;
            border-radius: 14px;
            padding: 16px 20px;
            color: #ffffff;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.5);
            animation: pulse-danger 1.5s infinite alternate;
        }

        @keyframes pulse-danger {
            0% { box-shadow: 0 0 15px rgba(239, 68, 68, 0.5); }
            100% { box-shadow: 0 0 30px rgba(239, 68, 68, 0.8); }
        }

        /* Grid Layout Utilities */
        .grid { display: grid; gap: 24px; }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }

        /* Card System */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.3);
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .card:hover {
            border-color: rgba(99, 102, 241, 0.4);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Metric Cards */
        .metric-card {
            position: relative;
            overflow: hidden;
        }

        .metric-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #fff;
        }

        .metric-value {
            font-size: 26px;
            font-weight: 800;
            color: #fff;
            margin: 12px 0 4px;
        }

        .metric-sub {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* Modern Tables */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            background: rgba(15, 23, 42, 0.6);
            color: var(--text-muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 14px 18px;
            font-weight: 700;
            border-bottom: 1px solid var(--border-color);
        }

        td {
            padding: 16px 18px;
            font-size: 14px;
            border-bottom: 1px solid rgba(51, 65, 85, 0.5);
            color: #e2e8f0;
        }

        tr:hover td {
            background: rgba(51, 65, 85, 0.3);
        }

        /* Badge Status */
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
        }

        .badge-success { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .badge-pending { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-danger { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
        .badge-info { background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.3); }

        /* Buttons & Forms */
        .btn {
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); }
        .btn-success { background: var(--success); color: #fff; }
        .btn-success:hover { background: #059669; }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-danger:hover { background: #dc2626; }
        .btn-secondary { background: #334155; color: #fff; }
        .btn-secondary:hover { background: #475569; }
        .btn-info { background: #0284c7; color: #fff; }
        .btn-info:hover { background: #0369a1; }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            background: #0f172a;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: #fff;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* Alert Styling */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .alert-error { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }

        @media (max-width: 1024px) {
            .grid-4, .grid-3 { grid-template-columns: repeat(2, 1fr); }
            .sidebar { width: 80px; }
            .brand-text, .nav-label, .sidebar-user .user-details { display: none; }
            .main-content { margin-left: 80px; }
            .quick-role-bar { display: none; }
        }

        @media (max-width: 768px) {
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="fa-solid fa-tower-broadcast"></i></div>
            <div class="brand-text">
                <h2>SIKAS-<span>RT</span></h2>
                <p>Smart RT & IoT ESP32</p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-label">Menu Utama</div>
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('tagihan.index') }}" class="nav-item {{ request()->routeIs('tagihan.*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-invoice-dollar" style="color: #38bdf8;"></i> <span>Tagihan Warga</span>
            </a>
            <a href="{{ route('iuran.index') }}" class="nav-item {{ request()->routeIs('iuran.*') ? 'active' : '' }}">
                <i class="fa-solid fa-receipt"></i> <span>Iuran Warga</span>
            </a>
            <a href="{{ route('kas.index') }}" class="nav-item {{ request()->routeIs('kas.*') ? 'active' : '' }}">
                <i class="fa-solid fa-cash-register"></i> <span>Laporan Kas RT</span>
            </a>

            <div class="nav-label" style="margin-top: 12px;">Kegiatan Warga</div>
            <a href="{{ route('ronda.index') }}" class="nav-item {{ request()->routeIs('ronda.*') ? 'active' : '' }}">
                <i class="fa-solid fa-shield-halved" style="color: #34d399;"></i> <span>Jadwal Ronda</span>
            </a>
            <a href="{{ route('posyandu.index') }}" class="nav-item {{ request()->routeIs('posyandu.*') ? 'active' : '' }}">
                <i class="fa-solid fa-heart-pulse" style="color: #f472b6;"></i> <span>Posyandu Warga</span>
            </a>
            <a href="{{ route('announcements.index') }}" class="nav-item {{ request()->routeIs('announcements.*') ? 'active' : '' }}">
                <i class="fa-solid fa-bullhorn"></i> <span>Pengumuman</span>
            </a>

            <div class="nav-label" style="margin-top: 12px;">Layanan & Ekonomi</div>
            <a href="{{ route('surat.index') }}" class="nav-item {{ request()->routeIs('surat.*') ? 'active' : '' }}">
                <i class="fa-solid fa-envelope-open-text" style="color: #a78bfa;"></i> <span>Surat Pengantar</span>
            </a>
            <a href="{{ route('umkm.index') }}" class="nav-item {{ request()->routeIs('umkm.*') ? 'active' : '' }}">
                <i class="fa-solid fa-store" style="color: #fbbf24;"></i> <span>UMKM Warga</span>
            </a>
            <a href="{{ route('bank_sampah.index') }}" class="nav-item {{ request()->routeIs('bank_sampah.*') ? 'active' : '' }}">
                <i class="fa-solid fa-recycle" style="color: #2dd4bf;"></i> <span>Bank Sampah</span>
            </a>

            <div class="nav-label" style="margin-top: 12px;">Akses & Portal IoT</div>
            <a href="{{ route('ktp.index') }}" class="nav-item {{ request()->routeIs('ktp.*') ? 'active' : '' }}" style="background: rgba(99, 102, 241, 0.08); border: 1px solid rgba(99, 102, 241, 0.25);">
                <i class="fa-solid fa-id-card" style="color: #818cf8;"></i> <span>e-KTP & Akses Portal</span>
            </a>
            <a href="{{ route('iot.index') }}" class="nav-item nav-iot {{ request()->routeIs('iot.*') ? 'active' : '' }}">
                <i class="fa-solid fa-microchip"></i> <span>Smart IoT & ESP32</span>
            </a>

            @if(in_array(auth()->user()->role, ['superadmin', 'bendahara', 'rt']))
            <div class="nav-label" style="margin-top: 12px;">Manajemen RT</div>
            <a href="{{ route('warga.index') }}" class="nav-item {{ request()->routeIs('warga.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i> <span>Data Warga</span>
            </a>
            @endif
        </nav>

        <div class="sidebar-user">
            <div class="user-info">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                <div class="user-details">
                    <h4>{{ Str::limit(auth()->user()->name, 14) }}</h4>
                    <span class="user-role-badge role-{{ auth()->user()->role }}">{{ auth()->user()->role }}</span>
                </div>
            </div>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="color: var(--text-muted);" title="Keluar">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
        </div>
    </aside>

    <!-- Main Section -->
    <main class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <div class="page-title">
                <h1>
                    @yield('page_title', 'Dashboard')
                    <span class="iot-online-badge"><span class="dot-pulse"></span> IoT Online</span>
                </h1>
            </div>

            <div class="topbar-actions">
                <!-- Panic Trigger Button -->
                <button type="button" class="btn-panic-quick" onclick="openPanicModal()">
                    <i class="fa-solid fa-triangle-exclamation"></i> Tombol Panik RT
                </button>

                <!-- Demo Quick Switcher -->
                <div class="quick-role-bar">
                    <span class="quick-role-label"><i class="fa-solid fa-bolt" style="color: #f59e0b;"></i> Demo:</span>
                    <a href="{{ route('quick-login', 'admin@kasrt.local') }}" class="btn-role-chip" style="{{ auth()->user()->role === 'superadmin' ? 'color:#fca5a5; font-weight:700;' : '' }}">Admin</a>
                    <a href="{{ route('quick-login', 'bendahara@kasrt.local') }}" class="btn-role-chip" style="{{ auth()->user()->role === 'bendahara' ? 'color:#6ee7b7; font-weight:700;' : '' }}">Bendahara</a>
                    <a href="{{ route('quick-login', 'rt@kasrt.local') }}" class="btn-role-chip" style="{{ auth()->user()->role === 'rt' ? 'color:#7dd3fc; font-weight:700;' : '' }}">RT</a>
                    <a href="{{ route('quick-login', 'budi@kasrt.local') }}" class="btn-role-chip" style="{{ auth()->user()->role === 'warga' ? 'color:#fde047; font-weight:700;' : '' }}">Warga</a>
                </div>
            </div>
        </header>

        <!-- Body Content -->
        <div class="content-body">
            @if(session('emergency_triggered'))
                <div class="emergency-banner">
                    <div>
                        <strong style="font-size: 16px;"><i class="fa-solid fa-triangle-exclamation"></i> ALARM DARURAT DIAKTIFKAN!</strong>
                        <p style="font-size: 13px; margin-top: 4px;">{{ session('emergency_triggered') }}</p>
                    </div>
                    <a href="{{ route('iot.index') }}" class="btn btn-secondary" style="background: rgba(0,0,0,0.4); border: 1px solid #fff;">Lihat Detail IoT</a>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Modal Panic Button -->
    <div id="modal-panic" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#1e293b; border:2px solid #ef4444; border-radius:18px; max-width:460px; width:90%; padding:28px; box-shadow:0 25px 50px rgba(239,68,68,0.4);">
            <div style="display:flex; align-items:center; gap:14px; margin-bottom:16px;">
                <div style="width:48px; height:48px; border-radius:12px; background:#ef4444; display:flex; align-items:center; justify-content:center; color:#fff; font-size:24px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 style="font-size:18px; color:#fff; font-weight:700;">Tombol Darurat Lingkungan</h3>
                    <p style="font-size:12px; color:#94a3b8;">Kirim sinyal sirene ke ESP32 Pos Keamanan & Pengurus RT</p>
                </div>
            </div>
            <form action="{{ route('iot.panic.trigger') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Jenis Situasi Darurat</label>
                    <select name="alert_type" class="form-control" required>
                        <option value="panic_button">Tombol Panik / Bantuan Cepat</option>
                        <option value="security">Pencurian / Gangguan Keamanan</option>
                        <option value="medical">Darurat Medis / Ambulans</option>
                        <option value="fire_alarm">Kebakaran / Asap Tebal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Keterangan Tambahan (Opsional)</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Contoh: Terlihat orang mencurigakan di depan pos / butuh pertolongan..."></textarea>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                    <button type="button" class="btn btn-secondary" onclick="closePanicModal()">Batal</button>
                    <button type="submit" class="btn btn-danger" style="box-shadow: 0 4px 15px rgba(239, 68, 68, 0.5);">
                        <i class="fa-solid fa-bell"></i> BUNYIKAN ALARM SEKARANG
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPanicModal() {
            document.getElementById('modal-panic').style.display = 'flex';
        }
        function closePanicModal() {
            document.getElementById('modal-panic').style.display = 'none';
        }
    </script>

    @stack('scripts')
</body>
</html>

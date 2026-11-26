<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - SIKAS-RT Smart IoT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --secondary: #0ea5e9;
            --bg-dark: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.85);
            --border: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; -webkit-tap-highlight-color: transparent; }
        
        body {
            background: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            position: relative;
            overflow-x: hidden;
        }

        .bg-glow {
            position: absolute;
            width: 450px;
            height: 450px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.22) 0%, rgba(15, 23, 42, 0) 70%);
            top: -120px;
            left: -120px;
            z-index: 1;
            pointer-events: none;
        }

        .bg-glow-2 {
            position: absolute;
            width: 450px;
            height: 450px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.2) 0%, rgba(15, 23, 42, 0) 70%);
            bottom: -120px;
            right: -120px;
            z-index: 1;
            pointer-events: none;
        }

        .login-wrapper {
            width: 100%;
            max-width: 920px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6);
            position: relative;
            z-index: 10;
        }

        /* Banner Desktop */
        .login-banner {
            padding: 44px 36px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.25) 0%, rgba(14, 165, 233, 0.2) 100%);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-logo .icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 50%, #8b5cf6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #fff;
            box-shadow: 0 6px 20px rgba(6, 182, 212, 0.4);
        }

        .brand-logo h1 { font-size: 24px; font-weight: 800; color: #fff; letter-spacing: -0.5px; }
        .brand-logo p { font-size: 12px; color: var(--text-muted); font-weight: 500; }

        .banner-text { margin: 28px 0; }
        .banner-text h2 { font-size: 22px; font-weight: 700; color: #fff; margin-bottom: 10px; line-height: 1.35; }
        .banner-text p { font-size: 13px; color: var(--text-muted); line-height: 1.6; }

        /* Role Switcher Box */
        .role-switcher-box {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 16px;
            margin-top: 20px;
        }

        .role-switcher-header {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #fbbf24;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .role-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .role-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            background: #1e293b;
            border: 1px solid var(--border);
            border-radius: 12px;
            color: #e2e8f0;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .role-btn i { font-size: 15px; width: 18px; text-align: center; }

        .role-btn:hover, .role-btn:active {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.2);
            color: #a5b4fc;
            transform: translateY(-1px);
        }

        /* Form Side */
        .login-form-container {
            padding: 44px 36px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Mobile Brand Header */
        .mobile-brand-header {
            display: none;
            text-align: center;
            margin-bottom: 24px;
        }

        .mobile-brand-header .icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 50%, #8b5cf6 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: #fff;
            box-shadow: 0 8px 25px rgba(6, 182, 212, 0.45);
            margin-bottom: 12px;
        }

        .mobile-brand-header h1 { font-size: 24px; font-weight: 800; color: #fff; }
        .mobile-brand-header p { font-size: 13px; color: var(--text-muted); margin-top: 2px; }

        .login-header { margin-bottom: 22px; }
        .login-header h3 { font-size: 22px; font-weight: 700; color: #fff; }
        .login-header p { font-size: 13px; color: var(--text-muted); margin-top: 4px; }

        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; }
        
        .input-icon-wrapper { position: relative; }
        .input-icon-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 15px;
        }

        .form-control {
            width: 100%;
            padding: 13px 14px 13px 44px;
            background: #0f172a;
            border: 1px solid var(--border);
            border-radius: 12px;
            color: #fff;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
            background: rgba(15, 23, 42, 0.9);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 8px;
        }

        .btn-submit:hover, .btn-submit:active {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.55);
        }

        .alert-danger {
            padding: 12px 16px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
            color: #f87171;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Mobile Role Switcher (Visible on Mobile) */
        .mobile-quick-login {
            display: none;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .mobile-quick-login-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #fbbf24;
            font-weight: 700;
            margin-bottom: 12px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .mobile-quick-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .mobile-role-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 12px 8px;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid var(--border);
            border-radius: 14px;
            color: #f1f5f9;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s;
            text-align: center;
        }

        .mobile-role-card i {
            font-size: 20px;
        }

        .mobile-role-card:active {
            transform: scale(0.96);
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.2);
        }

        .card-superadmin i { color: #f87171; }
        .card-bendahara i { color: #34d399; }
        .card-rt i { color: #38bdf8; }
        .card-warga i { color: #fbbf24; }

        /* Media Queries for Mobile Responsiveness */
        @media (max-width: 768px) {
            body { padding: 12px; }
            .login-wrapper {
                grid-template-columns: 1fr;
                border-radius: 20px;
                max-width: 440px;
            }
            .login-banner { display: none; }
            .login-form-container { padding: 28px 20px; }
            .mobile-brand-header { display: block; }
            .mobile-quick-login { display: block; }
            .login-header { text-align: center; margin-bottom: 18px; }
        }
    </style>
</head>
<body>

    <div class="bg-glow"></div>
    <div class="bg-glow-2"></div>

    <div class="login-wrapper">
        <!-- Banner Side (Desktop) -->
        <div class="login-banner">
            <div class="brand-logo">
                <div class="icon"><i class="fa-solid fa-tower-broadcast"></i></div>
                <div>
                    <h1>SIKAS-RT</h1>
                    <p>Smart RT & IoT ESP32</p>
                </div>
            </div>

            <div class="banner-text">
                <h2>Transparansi Kas & Otomasi Lingkungan RT</h2>
                <p>Sistem cerdas pengelolaan kas RT terpadu berbasis IoT ESP32 dengan fitur Portal RFID, Tap-to-Pay Iuran, Smart Kotak Kas, dan Tombol Darurat Siskamling.</p>

                <div class="role-switcher-box">
                    <div class="role-switcher-header">
                        <i class="fa-solid fa-bolt" style="color: #f59e0b;"></i> Login Cepat Demo (1-Klik)
                    </div>
                    <div class="role-grid">
                        <button type="button" onclick="promptPengurusAccess('admin@kasrt.local', 'Superadmin')" class="role-btn" style="cursor: pointer; width: 100%; text-align: left;">
                            <i class="fa-solid fa-user-shield" style="color: #f87171;"></i> Superadmin 🔒
                        </button>
                        <button type="button" onclick="promptPengurusAccess('bendahara@kasrt.local', 'Bendahara')" class="role-btn" style="cursor: pointer; width: 100%; text-align: left;">
                            <i class="fa-solid fa-calculator" style="color: #34d399;"></i> Bendahara 🔒
                        </button>
                        <button type="button" onclick="promptPengurusAccess('rt@kasrt.local', 'Ketua RT')" class="role-btn" style="cursor: pointer; width: 100%; text-align: left;">
                            <i class="fa-solid fa-crown" style="color: #38bdf8;"></i> Ketua RT 🔒
                        </button>
                        <a href="{{ route('quick-login', 'budi@kasrt.local') }}" class="role-btn">
                            <i class="fa-solid fa-house-user" style="color: #fbbf24;"></i> Warga (Bebas)
                        </a>
                    </div>
                </div>
            </div>

            <div style="font-size: 12px; color: #64748b; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> SIKAS-RT &bull; Laravel 12 & ESP32 IoT
            </div>
        </div>

        <!-- Login Form Side (Desktop & Mobile) -->
        <div class="login-form-container">
            <!-- Mobile Brand Header -->
            <div class="mobile-brand-header">
                <div class="icon"><i class="fa-solid fa-tower-broadcast"></i></div>
                <h1>SIKAS-RT</h1>
                <p>Smart RT & IoT ESP32 Gateway</p>
            </div>

            <div class="login-header">
                <h3>Masuk Akun</h3>
                <p>Masukkan email & kata sandi atau gunakan Login Cepat Demo di bawah</p>
            </div>

            @if(session('error'))
                <div class="alert-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Email Terdaftar</label>
                    <div class="input-icon-wrapper">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" class="form-control" placeholder="contoh: admin@kasrt.local" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label>Kata Sandi</label>
                    <div class="input-icon-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 22px;">
                    <label style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Kode Akses Pengurus</span>
                        <span style="font-size: 11px; color: #fbbf24; font-weight: 600;"><i class="fa-solid fa-shield-halved"></i> Khusus Admin/RT/Bendahara</span>
                    </label>
                    <div class="input-icon-wrapper">
                        <i class="fa-solid fa-key"></i>
                        <input type="password" name="access_code" class="form-control" placeholder="PIN Pengurus (Warga kosongkan)">
                    </div>
                    @if($errors->has('access_code'))
                        <div style="font-size: 11.5px; color: #f87171; margin-top: 4px;">{{ $errors->first('access_code') }}</div>
                    @endif
                </div>

                <button type="submit" class="btn-submit">
                    <span>Masuk Aplikasi</span> <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <!-- Mobile Quick Login (Visible on Mobile Devices) -->
            <div class="mobile-quick-login">
                <div class="mobile-quick-login-title">
                    <i class="fa-solid fa-bolt"></i> Login Cepat Demo (1-Klik)
                </div>
                <div class="mobile-quick-grid">
                    <button type="button" onclick="promptPengurusAccess('admin@kasrt.local', 'Superadmin')" class="mobile-role-card card-superadmin" style="cursor: pointer; width: 100%;">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>Superadmin 🔒</span>
                    </button>
                    <button type="button" onclick="promptPengurusAccess('bendahara@kasrt.local', 'Bendahara')" class="mobile-role-card card-bendahara" style="cursor: pointer; width: 100%;">
                        <i class="fa-solid fa-calculator"></i>
                        <span>Bendahara 🔒</span>
                    </button>
                    <button type="button" onclick="promptPengurusAccess('rt@kasrt.local', 'Ketua RT')" class="mobile-role-card card-rt" style="cursor: pointer; width: 100%;">
                        <i class="fa-solid fa-crown"></i>
                        <span>Ketua RT 🔒</span>
                    </button>
                    <a href="{{ route('quick-login', 'budi@kasrt.local') }}" class="mobile-role-card card-warga">
                        <i class="fa-solid fa-house-user"></i>
                        <span>Warga RT</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Kode Akses Pengurus RT -->
    <div id="modalAccessCode" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(8px); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
        <div style="background: #1e293b; border: 1px solid #475569; border-radius: 20px; max-width: 420px; width: 100%; padding: 26px; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.6); position: relative;">
            <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 14px;">
                <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); display: flex; align-items: center; justify-content: center; color: #fbbf24; font-size: 22px; flex-shrink: 0;">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h3 style="font-size: 17px; font-weight: 700; color: #fff;">Verifikasi Kode Akses</h3>
                    <p style="font-size: 12.5px; color: var(--text-muted); margin-top: 2px;">Masuk sebagai <strong id="macRoleLabel" style="color: #38bdf8;">Superadmin</strong></p>
                </div>
            </div>

            <p style="font-size: 12px; color: #94a3b8; line-height: 1.5; margin-bottom: 16px;">
                Untuk keamanan data RT, akun pengurus (Superadmin, Bendahara, dan Ketua RT) dilindungi dengan kode akses keamanan.
            </p>

            <div class="form-group" style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 6px;">
                    Masukkan Kode Akses Pengurus
                </label>
                <div class="input-icon-wrapper">
                    <i class="fa-solid fa-key" style="color: #64748b;"></i>
                    <input type="password" id="macInputCode" class="form-control" placeholder="Contoh: RT2026" autocomplete="off" style="font-size: 14px; padding: 12px 14px 12px 42px;">
                </div>
                <div style="font-size: 11px; color: #fbbf24; margin-top: 6px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-circle-info"></i> Kode Akses Pengurus: <strong>RT2026</strong>
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeAccessModal()" style="flex: 1; padding: 12px; background: #334155; border: none; border-radius: 12px; color: #fff; font-size: 13px; font-weight: 600; cursor: pointer;">
                    Batal
                </button>
                <button type="button" onclick="submitAccessCode()" style="flex: 1.5; padding: 12px; background: linear-gradient(135deg, #6366f1, #4f46e5); border: none; border-radius: 12px; color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <span>Verifikasi & Masuk</span> <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentTargetEmail = '';

        function promptPengurusAccess(email, roleName) {
            currentTargetEmail = email;
            document.getElementById('macRoleLabel').textContent = roleName;
            const input = document.getElementById('macInputCode');
            input.value = '';
            document.getElementById('modalAccessCode').style.display = 'flex';
            setTimeout(() => input.focus(), 100);
        }

        function closeAccessModal() {
            document.getElementById('modalAccessCode').style.display = 'none';
            currentTargetEmail = '';
        }

        function submitAccessCode() {
            const code = document.getElementById('macInputCode').value.trim();
            if (!code) {
                alert('Silakan masukkan Kode Akses Pengurus!');
                document.getElementById('macInputCode').focus();
                return;
            }
            const url = `{{ url('/quick-login') }}/${encodeURIComponent(currentTargetEmail)}?code=${encodeURIComponent(code)}`;
            window.location.href = url;
        }

        document.getElementById('macInputCode').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                submitAccessCode();
            } else if (e.key === 'Escape') {
                closeAccessModal();
            }
        });
    </script>

</body>
</html>

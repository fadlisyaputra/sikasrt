<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIKAS-RT Smart IoT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body {
            background: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        .bg-glow {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.25) 0%, rgba(15, 23, 42, 0) 70%);
            top: -100px;
            left: -100px;
            z-index: 1;
        }

        .bg-glow-2 {
            position: absolute;
            width: 450px;
            height: 450px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.2) 0%, rgba(15, 23, 42, 0) 70%);
            bottom: -150px;
            right: -150px;
            z-index: 1;
        }

        .login-wrapper {
            width: 100%;
            max-width: 900px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(16px);
            border: 1px solid #334155;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 10;
        }

        .login-banner {
            padding: 40px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(14, 165, 233, 0.2) 100%);
            border-right: 1px solid #334155;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo .icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #fff;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }

        .brand-logo h1 { font-size: 22px; font-weight: 800; color: #fff; }
        .brand-logo p { font-size: 12px; color: #94a3b8; }

        .banner-text h2 { font-size: 24px; font-weight: 700; color: #fff; margin-bottom: 12px; line-height: 1.3; }
        .banner-text p { font-size: 14px; color: #94a3b8; line-height: 1.6; }

        .role-switcher-box {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 16px;
            margin-top: 24px;
        }

        .role-switcher-box h4 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #f59e0b;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .role-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .role-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 10px;
            color: #e2e8f0;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .role-btn:hover {
            border-color: #6366f1;
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
        }

        .login-form-container {
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header { margin-bottom: 24px; }
        .login-header h3 { font-size: 22px; font-weight: 700; color: #fff; }
        .login-header p { font-size: 13px; color: #94a3b8; margin-top: 4px; }

        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #94a3b8; margin-bottom: 6px; }
        
        .input-icon-wrapper {
            position: relative;
        }

        .input-icon-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px 12px 42px;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 12px;
            color: #fff;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2); }

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
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(99, 102, 241, 0.5); }

        .alert-danger {
            padding: 12px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 10px;
            color: #f87171;
            font-size: 13px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .login-wrapper { grid-template-columns: 1fr; }
            .login-banner { display: none; }
        }
    </style>
</head>
<body>

    <div class="bg-glow"></div>
    <div class="bg-glow-2"></div>

    <div class="login-wrapper">
        <!-- Banner Side -->
        <div class="login-banner">
            <div class="brand-logo">
                <div class="icon" style="background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 50%, #8b5cf6 100%);"><i class="fa-solid fa-tower-broadcast"></i></div>
                <div>
                    <h1>SIKAS-RT</h1>
                    <p>Smart RT & IoT ESP32</p>
                </div>
            </div>

            <div class="banner-text">
                <h2>Transparansi Kas & Otomasi Lingkungan RT</h2>
                <p>Sistem cerdas pengelolaan kas RT terpadu berbasis IoT ESP32 dengan fitur Portal RFID, Tap-to-Pay Iuran, Smart Kotak Kas, dan Tombol Darurat Siskamling.</p>

                <div class="role-switcher-box">
                    <h4><i class="fa-solid fa-bolt" style="color: #f59e0b;"></i> Login Cepat Demo Multi-Role</h4>
                    <div class="role-grid">
                        <a href="{{ route('quick-login', 'admin@kasrt.local') }}" class="role-btn">
                            <i class="fa-solid fa-user-shield" style="color: #f87171;"></i> Superadmin
                        </a>
                        <a href="{{ route('quick-login', 'bendahara@kasrt.local') }}" class="role-btn">
                            <i class="fa-solid fa-calculator" style="color: #34d399;"></i> Bendahara
                        </a>
                        <a href="{{ route('quick-login', 'rt@kasrt.local') }}" class="role-btn">
                            <i class="fa-solid fa-crown" style="color: #38bdf8;"></i> Ketua RT
                        </a>
                        <a href="{{ route('quick-login', 'budi@kasrt.local') }}" class="role-btn">
                            <i class="fa-solid fa-house-user" style="color: #fbbf24;"></i> Warga
                        </a>
                    </div>
                </div>
            </div>

            <div style="font-size: 12px; color: #64748b;">
                SIKAS-RT &bull; Powered by Laravel 12 & ESP32 Microcontroller
            </div>
        </div>

        <!-- Login Form Side -->
        <div class="login-form-container">
            <div class="login-header">
                <h3>Masuk Akun</h3>
                <p>Silakan masukkan email dan kata sandi Anda</p>
            </div>

            @if($errors->any())
                <div class="alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}
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

                <div class="form-group" style="margin-bottom: 24px;">
                    <label>Kata Sandi</label>
                    <div class="input-icon-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <span>Masuk Aplikasi</span> <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

</body>
</html>

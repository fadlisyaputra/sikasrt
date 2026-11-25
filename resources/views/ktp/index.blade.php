@extends('layouts.app')

@section('title', 'e-KTP & Akses Portal')
@section('page_title', 'e-KTP & Manajemen Akses Portal Perumahan')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Status Banner -->
    @if($registeredUid)
    <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(6, 182, 212, 0.2) 100%); border: 1px solid #10b981; border-radius: 16px; padding: 20px 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #34d399;">
                    <i class="fa-solid fa-id-card-clip"></i>
                </div>
                <div>
                    <h3 style="font-size: 18px; font-weight: 800; color: #fff;">e-KTP Anda Sudah Terdaftar & Siap Digunakan!</h3>
                    <p style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">
                        UID KTP: <strong style="font-family: monospace; color: #38bdf8; font-size: 14px;">{{ $registeredUid }}</strong> &bull; Status: <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> AKSES PORTAL AKTIF</span>
                    </p>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="openScanModal()" class="btn btn-secondary" style="font-size: 13px;">
                    <i class="fa-solid fa-arrows-rotate"></i> Ganti e-KTP
                </button>
                <form action="{{ route('ktp.unpair') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mencabut e-KTP ini dari akun Anda?')" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-danger" style="font-size: 13px;">
                        <i class="fa-solid fa-trash-can"></i> Hapus KTP
                    </button>
                </form>
            </div>
        </div>
    </div>
    @else
    <div style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(239, 68, 68, 0.15) 100%); border: 1px solid #f59e0b; border-radius: 16px; padding: 20px 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(245, 158, 11, 0.2); border: 1px solid #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fbbf24;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 style="font-size: 18px; font-weight: 800; color: #fff;">e-KTP Belum Terdaftar di Akun Anda!</h3>
                    <p style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">
                        Untuk dapat membuka portal perumahan secara otomatis, silakan scan atau daftarkan chip RFID/NFC e-KTP Anda sekarang.
                    </p>
                </div>
            </div>
            <button type="button" onclick="openScanModal()" class="btn btn-primary" style="padding: 10px 20px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);">
                <i class="fa-solid fa-qrcode"></i> Scan e-KTP Sekarang
            </button>
        </div>
    </div>
    @endif

    <div class="grid grid-2" style="align-items: start;">

        <!-- Kolom Kiri: Kartu KTP Digital & Fitur Scan -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            
            <!-- Kartu e-KTP Visual Digital -->
            <div class="card" style="padding: 0; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.1); background: transparent;">
                <div style="background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 60%, #1e1b4b 100%); border-radius: 16px; padding: 24px; color: #fff; position: relative; box-shadow: 0 15px 35px rgba(0,0,0,0.5); border: 1px solid rgba(56, 189, 248, 0.3);">
                    <!-- Watermark Garuda / Chip -->
                    <div style="position: absolute; right: 20px; top: 20px; opacity: 0.15; font-size: 110px; pointer-events: none;">
                        <i class="fa-solid fa-id-card"></i>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.15); padding-bottom: 12px; margin-bottom: 16px;">
                        <div>
                            <div style="font-size: 10px; font-weight: 800; letter-spacing: 1.5px; color: #93c5fd; text-transform: uppercase;">Republik Indonesia</div>
                            <div style="font-size: 14px; font-weight: 800; letter-spacing: 0.5px;">KARTU TANDA PENDUDUK ELEKTRONIK (e-KTP)</div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-wifi" style="transform: rotate(90deg); color: #38bdf8; font-size: 18px;" title="NFC / RFID Contactless"></i>
                            <span style="font-size: 10px; background: rgba(56, 189, 248, 0.2); border: 1px solid #38bdf8; padding: 2px 6px; border-radius: 4px; color: #38bdf8; font-weight: 700;">NFC ISO 14443</span>
                        </div>
                    </div>

                    <!-- Smart Card Chip Icon -->
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
                        <div style="width: 44px; height: 32px; background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%); border-radius: 6px; border: 1px solid #fef08a; display: flex; align-items: center; justify-content: center; box-shadow: inset 0 0 5px rgba(0,0,0,0.3);">
                            <div style="width: 28px; height: 18px; border: 1px solid rgba(0,0,0,0.3); border-radius: 3px;"></div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 10px; color: #94a3b8; text-transform: uppercase;">ID Portal Perumahan</div>
                            <div style="font-size: 12px; font-weight: 700; color: #38bdf8;">SIKAS-RT SMART GATE</div>
                        </div>
                    </div>

                    <!-- User Detail on Card -->
                    <div style="display: flex; gap: 16px; align-items: flex-start;">
                        <div style="width: 65px; height: 80px; border-radius: 8px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; color: #94a3b8; flex-shrink: 0;">
                            <i class="fa-solid fa-user" style="font-size: 28px; color: #cbd5e1;"></i>
                            <span style="font-size: 9px; font-weight: 700; color: #38bdf8;">WARGA</span>
                        </div>

                        <div style="flex: 1; font-size: 13px; line-height: 1.5;">
                            <div style="display: flex; flex-direction: column; margin-bottom: 6px;">
                                <span style="font-size: 10px; color: #94a3b8; text-transform: uppercase;">Nama Lengkap</span>
                                <strong style="font-size: 15px; color: #fff; letter-spacing: 0.3px;">{{ strtoupper($user->name) }}</strong>
                            </div>
                            <div style="display: flex; flex-direction: column; margin-bottom: 6px;">
                                <span style="font-size: 10px; color: #94a3b8; text-transform: uppercase;">Alamat / Hunian</span>
                                <span style="color: #e2e8f0; font-size: 12px;">{{ $user->address ?? 'RT 01 / RW 05' }} ({{ $user->rt_rw }})</span>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; padding-top: 8px; border-top: 1px dashed rgba(255,255,255,0.15);">
                                <div>
                                    <span style="font-size: 10px; color: #94a3b8; text-transform: uppercase;">UID KTP Elektronik</span>
                                    <div style="font-family: monospace; font-size: 15px; font-weight: 800; color: {{ $registeredUid ? '#34d399' : '#f87171' }};">
                                        {{ $registeredUid ?? 'BELUM TERDAFTAR' }}
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span style="font-size: 10px; color: #94a3b8; text-transform: uppercase;">Akses Portal</span>
                                    <div>
                                        @if($registeredUid)
                                            <span style="color: #34d399; font-weight: 700; font-size: 12px;"><i class="fa-solid fa-unlock"></i> DIIZINKAN</span>
                                        @else
                                            <span style="color: #f87171; font-weight: 700; font-size: 12px;"><i class="fa-solid fa-lock"></i> DITOLAK</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Pendaftaran e-KTP -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-nfc-symbol" style="color: var(--primary);"></i>
                        Pendaftaran & Scan e-KTP Baru
                    </h3>
                </div>

                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">
                    e-KTP Indonesia memuat chip RFID/NFC internal (frekuensi 13.56 MHz) dengan Unique ID (UID) permanen. Daftarkan UID e-KTP Anda ke akun ini agar palang pintu portal otomatis terbuka saat Anda men-tap KTP.
                </p>

                <!-- Pilihan 3 Metode Registrasi -->
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    
                    <!-- Metode 1: Scan via Smartphone (Web NFC) -->
                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px;">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                            <div style="display: flex; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(99, 102, 241, 0.2); color: #818cf8; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                                    <i class="fa-solid fa-mobile-screen-button"></i>
                                </div>
                                <div>
                                    <h4 style="font-size: 14px; font-weight: 700; color: #fff;">Metode 1: Scan via Smartphone (NFC)</h4>
                                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                        Buka web SIKAS-RT di Chrome HP Android ber-NFC, lalu tempelkan e-KTP Anda ke bodi belakang smartphone.
                                    </p>
                                </div>
                            </div>
                            <button type="button" onclick="startWebNfcScan()" class="btn btn-primary" style="font-size: 12px; padding: 8px 14px; white-space: nowrap;">
                                <i class="fa-solid fa-wifi"></i> Scan NFC HP
                            </button>
                        </div>
                        <div id="webNfcStatus" style="display: none; margin-top: 10px; font-size: 12px; padding: 8px 12px; border-radius: 8px;"></div>
                    </div>

                    <!-- Metode 2: Live Pairing via ESP32 Reader -->
                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px;">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                            <div style="display: flex; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(6, 182, 212, 0.2); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                                    <i class="fa-solid fa-microchip"></i>
                                </div>
                                <div>
                                    <h4 style="font-size: 14px; font-weight: 700; color: #fff;">Metode 2: Scan via Alat ESP32 RT</h4>
                                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                        Aktifkan mode pendaftaran, lalu tempelkan fisik e-KTP Anda ke modul RFID reader ESP32 di Pos RT / Portal.
                                    </p>
                                </div>
                            </div>
                            <button type="button" id="btnEsp32Pairing" onclick="startEsp32Pairing()" class="btn btn-info" style="font-size: 12px; padding: 8px 14px; white-space: nowrap;">
                                <i class="fa-solid fa-satellite-dish"></i> Scan di ESP32
                            </button>
                        </div>

                        <!-- Pairing Radar Animation Box -->
                        <div id="esp32PairingBox" style="display: none; margin-top: 14px; background: rgba(6, 182, 212, 0.1); border: 1px solid #06b6d4; border-radius: 10px; padding: 14px; text-align: center;">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 8px;">
                                <div class="dot-pulse" style="background: #06b6d4; width: 12px; height: 12px;"></div>
                                <span style="font-weight: 700; color: #38bdf8; font-size: 13px;">MODE SCAN ESP32 AKTIF</span>
                                <span id="pairingTimer" style="font-family: monospace; font-weight: 800; color: #fbbf24; background: rgba(0,0,0,0.3); padding: 2px 8px; border-radius: 6px; font-size: 12px;">60s</span>
                            </div>
                            <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 10px;">
                                📡 Menunggu Anda menempelkan e-KTP ke alat ESP32 terdekat... UID akan otomatis tersimpan ke akun Anda!
                            </p>
                            <button type="button" onclick="cancelEsp32Pairing()" class="btn btn-secondary" style="font-size: 11px; padding: 4px 10px;">
                                <i class="fa-solid fa-xmark"></i> Batalkan
                            </button>
                        </div>
                    </div>

                    <!-- Metode 3: Input Manual UID KTP -->
                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px;">
                        <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(16, 185, 129, 0.2); color: #34d399; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                                <i class="fa-solid fa-keyboard"></i>
                            </div>
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: #fff;">Metode 3: Input UID KTP Manual</h4>
                                <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                    Masukkan kode UID e-KTP Anda (misal didapat dari USB Card Reader atau NFC Tools app).
                                </p>
                            </div>
                        </div>

                        <form action="{{ route('ktp.register') }}" method="POST">
                            @csrf
                            <div style="display: flex; gap: 8px;">
                                <input type="text" name="rfid_uid" id="manual_rfid_uid" class="form-control" placeholder="Contoh: 93:B4:72:A1" value="{{ $registeredUid }}" required style="font-family: monospace; text-transform: uppercase; font-weight: 700; letter-spacing: 1px;">
                                <button type="submit" class="btn btn-success" style="font-size: 13px; font-weight: 700; padding: 0 18px; white-space: nowrap;">
                                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

        </div>

        <!-- Kolom Kanan: Simulator Palang Portal Real-Time & Riwayat Akses -->
        <div style="display: flex; flex-direction: column; gap: 24px;">

            <!-- Simulator Portal Perumahan -->
            <div class="card" style="border: 1px solid rgba(6, 182, 212, 0.4); background: linear-gradient(180deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%);">
                <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                    <div>
                        <h3 class="card-title" style="color: #38bdf8;">
                            <i class="fa-solid fa-torii-gate"></i> Simulator Palang Pintu Portal Perumahan
                        </h3>
                        <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                            Uji proteksi sistem: KTP yang terdaftar akan membuka portal, sedangkan KTP yang belum terdaftar langsung DITOLAK!
                        </p>
                    </div>
                    <span class="badge badge-success"><i class="fa-solid fa-shield-halved"></i> Proteksi Aktif</span>
                </div>

                <!-- Visual Animasi Palang Pintu & Lampu Lalu Lintas -->
                <div style="background: #090d16; border-radius: 14px; padding: 24px; margin: 16px 0; border: 1px solid var(--border-color); position: relative; overflow: hidden;">
                    
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
                        
                        <!-- Visual Tiang Palang Pintu Portal (CSS Animated) -->
                        <div style="display: flex; align-items: flex-end; gap: 10px; min-height: 140px; flex: 1;">
                            
                            <!-- Tiang Motor Barrier Gate -->
                            <div style="width: 44px; height: 110px; background: linear-gradient(180deg, #f59e0b 0%, #b45309 100%); border-radius: 8px 8px 0 0; position: relative; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding-top: 8px; box-shadow: 0 10px 20px rgba(0,0,0,0.5);">
                                
                                <!-- Lampu Indikator Traffic Light -->
                                <div style="background: #000; border-radius: 12px; padding: 4px; display: flex; flex-direction: column; gap: 4px; border: 1px solid #334155;">
                                    <div id="lightRed" style="width: 12px; height: 12px; border-radius: 50%; background: #ef4444; box-shadow: 0 0 10px #ef4444;"></div>
                                    <div id="lightGreen" style="width: 12px; height: 12px; border-radius: 50%; background: #064e3b;"></div>
                                </div>

                                <!-- Poros Putar Palang -->
                                <div style="position: absolute; right: -12px; top: 30px; width: 24px; height: 24px; border-radius: 50%; background: #334155; border: 3px solid #64748b; z-index: 10;">
                                    <!-- Palang Pintu Bergaris Merah Putih -->
                                    <div id="barrierArm" style="position: absolute; left: 10px; top: 7px; width: 170px; height: 10px; background: repeating-linear-gradient(45deg, #ef4444, #ef4444 15px, #ffffff 15px, #ffffff 30px); border-radius: 5px; transform-origin: 0% 50%; transform: rotate(0deg); transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 10px rgba(0,0,0,0.4);">
                                    </div>
                                </div>

                                <div style="margin-top: auto; margin-bottom: 6px; font-size: 8px; font-weight: 800; color: #fff; letter-spacing: 0.5px;">GATE</div>
                            </div>

                            <!-- Area Jalan & Garis Sensor RFID -->
                            <div style="flex: 1; border-bottom: 3px dashed #64748b; height: 30px; display: flex; align-items: center; justify-content: center; position: relative;">
                                <div style="position: absolute; top: -16px; font-size: 11px; color: var(--text-muted); font-weight: 600;">
                                    <i class="fa-solid fa-car-side"></i> JALUR MASUK WARGA
                                </div>
                            </div>
                        </div>

                        <!-- Mini Layar OLED Simulator (Tampilan Fisik di Alat ESP32) -->
                        <div style="width: 170px; background: #000; border: 3px solid #334155; border-radius: 10px; padding: 10px; font-family: monospace; color: #38bdf8; box-shadow: inset 0 0 15px rgba(6, 182, 212, 0.2);">
                            <div style="font-size: 9px; color: #64748b; border-bottom: 1px solid #1e293b; padding-bottom: 2px; margin-bottom: 6px; display: flex; justify-content: space-between;">
                                <span>OLED 128x64</span>
                                <span id="oledStatusBadge" style="color: #10b981;">STANDBY</span>
                            </div>
                            <div id="oledLine1" style="font-size: 11px; font-weight: bold; color: #fff;">=== SIKAS-RT ===</div>
                            <div id="oledLine2" style="font-size: 10px; margin-top: 4px; color: #38bdf8;">TEMPELKAN e-KTP</div>
                            <div id="oledLine3" style="font-size: 9px; margin-top: 4px; color: #94a3b8;">Portal Siaga</div>
                        </div>

                    </div>

                    <!-- Status Response Banner -->
                    <div id="portalFeedback" style="display: none; margin-top: 16px; padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 600;"></div>
                </div>

                <!-- Tombol-Tombol Pengujian Interaktif -->
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Uji Coba Tap Kartu:</div>
                    
                    <div class="grid grid-2" style="gap: 10px;">
                        <!-- Test 1: Tap KTP Terdaftar -->
                        <button type="button" onclick="testTapPortal('{{ $registeredUid ?? '' }}')" class="btn btn-success" style="font-weight: 700; font-size: 13px; padding: 12px;">
                            <i class="fa-solid fa-id-card"></i> 1. Uji Tap e-KTP Saya (Buka Portal)
                        </button>

                        <!-- Test 2: Tap KTP Belum Terdaftar -->
                        <button type="button" onclick="testTapPortal('99:88:77:66')" class="btn btn-danger" style="font-weight: 700; font-size: 13px; padding: 12px;">
                            <i class="fa-solid fa-ban"></i> 2. Uji KTP Liar / Asing (Akses Ditolak)
                        </button>
                    </div>

                    <!-- Test 3: Kustom UID Tap -->
                    <div style="display: flex; gap: 8px; margin-top: 4px;">
                        <input type="text" id="customTestUid" class="form-control" placeholder="Ketik UID Bebas (misal: 4A:8C:31:E9)" style="font-family: monospace; font-size: 13px; text-transform: uppercase;">
                        <button type="button" onclick="testTapPortal(document.getElementById('customTestUid').value)" class="btn btn-secondary" style="font-size: 13px; white-space: nowrap;">
                            <i class="fa-solid fa-magnifying-glass"></i> Tes UID
                        </button>
                    </div>
                </div>
            </div>

            <!-- Riwayat Log Akses Portal User -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-clock-rotate-left" style="color: var(--secondary);"></i>
                        Riwayat Akses Portal Saya
                    </h3>
                </div>

                @if($accessLogs->isEmpty())
                <div style="text-align: center; padding: 30px; color: var(--text-muted);">
                    <i class="fa-solid fa-door-closed" style="font-size: 36px; margin-bottom: 10px; opacity: 0.4;"></i>
                    <p style="font-size: 13px;">Belum ada riwayat akses portal tercatat untuk akun Anda.</p>
                </div>
                @else
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-muted); text-align: left;">
                                <th style="padding: 10px 8px;">Waktu</th>
                                <th style="padding: 10px 8px;">UID KTP</th>
                                <th style="padding: 10px 8px;">Status</th>
                                <th style="padding: 10px 8px;">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accessLogs as $log)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td style="padding: 10px 8px; color: var(--text-muted); font-size: 12px;">
                                    {{ \Carbon\Carbon::parse($log->logged_at)->translatedFormat('d M H:i:s') }}
                                </td>
                                <td style="padding: 10px 8px; font-family: monospace; font-weight: 700; color: #38bdf8;">
                                    {{ $log->rfid_uid ?? '-' }}
                                </td>
                                <td style="padding: 10px 8px;">
                                    @if($log->status === 'success')
                                        <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Dibuka</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fa-solid fa-circle-xmark"></i> Ditolak</span>
                                    @endif
                                </td>
                                <td style="padding: 10px 8px; color: #cbd5e1; font-size: 12px;">
                                    {{ $log->message }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

        </div>

    </div>

</div>

<!-- Audio Feedback Simulator (Web Audio API) -->
<script>
    // Sintesis Bunyi Beep Buzzer menggunakan Web Audio API murni (tanpa file mp3 luar)
    function playBuzzerTone(type) {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();

            if (type === 'success') {
                // Beep Sukses: 1x Bip Tinggi Ceria
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, ctx.currentTime); // Nada A5
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.2);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.2);
            } else if (type === 'error') {
                // Beep Error: 3x Bip Cepat Rendah (Warning)
                [0, 0.12, 0.24].forEach(timeOffset => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(320, ctx.currentTime + timeOffset);
                    gain.gain.setValueAtTime(0.2, ctx.currentTime + timeOffset);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + timeOffset + 0.09);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(ctx.currentTime + timeOffset);
                    osc.stop(ctx.currentTime + timeOffset + 0.09);
                });
            } else if (type === 'pair') {
                // Beep Pairing Sukses: 2x Melodic
                [ [659, 0], [880, 0.15] ].forEach(([freq, offset]) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, ctx.currentTime + offset);
                    gain.gain.setValueAtTime(0.15, ctx.currentTime + offset);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + offset + 0.12);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(ctx.currentTime + offset);
                    osc.stop(ctx.currentTime + offset + 0.12);
                });
            }
        } catch(e) {
            console.log('Web Audio tone:', e);
        }
    }

    // =========================================================================
    // 1. SIMULATOR PALANG GERBANG PORTAL (LIVE TEST)
    // =========================================================================
    function testTapPortal(uidToTest) {
        if (!uidToTest || uidToTest.trim() === '') {
            alert('Anda belum memiliki e-KTP terdaftar! Silakan daftarkan e-KTP Anda terlebih dahulu atau gunakan Uji Coba KTP Liar.');
            return;
        }

        const oledLine1 = document.getElementById('oledLine1');
        const oledLine2 = document.getElementById('oledLine2');
        const oledLine3 = document.getElementById('oledLine3');
        const oledStatus = document.getElementById('oledStatusBadge');
        const feedback = document.getElementById('portalFeedback');
        const arm = document.getElementById('barrierArm');
        const lightRed = document.getElementById('lightRed');
        const lightGreen = document.getElementById('lightGreen');

        // Indikasi Sedang Scan
        oledLine1.innerText = 'MEMBACA e-KTP...';
        oledLine2.innerText = uidToTest;
        oledLine3.innerText = 'Verifikasi Server...';
        oledStatus.innerText = 'CHECKING';
        oledStatus.style.color = '#fbbf24';

        fetch('{{ route("ktp.simulate.portal") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ rfid_uid: uidToTest })
        })
        .then(res => res.json())
        .then(data => {
            feedback.style.display = 'block';

            if (data.authorized) {
                // ==================== AKSES DITERIMA (BUKA GERBANG) ====================
                playBuzzerTone('success');

                // Visual Lampu Hijau
                lightRed.style.background = '#450a0a';
                lightRed.style.boxShadow = 'none';
                lightGreen.style.background = '#10b981';
                lightGreen.style.boxShadow = '0 0 12px #10b981';

                // Buka Palang Pintu (Putar 75 derajat ke atas)
                arm.style.transform = 'rotate(-75deg)';

                // Tampilan OLED Mini
                oledLine1.innerText = data.display?.line1 || 'PORTAL TERBUKA';
                oledLine2.innerText = data.display?.line2 || data.user_name || 'SILAKAN MASUK';
                oledLine3.innerText = data.display?.line3 || 'AKSES DISETUJUI';
                oledStatus.innerText = 'GRANTED';
                oledStatus.style.color = '#10b981';

                // Banner Feedback
                feedback.className = 'alert alert-success';
                feedback.style.background = 'rgba(16, 185, 129, 0.15)';
                feedback.style.border = '1px solid #10b981';
                feedback.style.color = '#34d399';
                feedback.innerHTML = `
                    <div style="display:flex; align-items:center; gap:10px;">
                        <i class="fa-solid fa-circle-check" style="font-size:22px;"></i>
                        <div>
                            <strong>AKSES DIIZINKAN! Palang Gerbang Terbuka.</strong><br>
                            Selamat Datang, <strong>${data.user_name}</strong> (${data.address || 'Warga RT'}). Akses RFID/NFC diverifikasi.
                        </div>
                    </div>
                `;

                // Otomatis tutup gerbang setelah 5 detik
                setTimeout(() => {
                    arm.style.transform = 'rotate(0deg)';
                    lightRed.style.background = '#ef4444';
                    lightRed.style.boxShadow = '0 0 10px #ef4444';
                    lightGreen.style.background = '#064e3b';
                    lightGreen.style.boxShadow = 'none';

                    oledLine1.innerText = '=== SIKAS-RT ===';
                    oledLine2.innerText = 'TEMPELKAN e-KTP';
                    oledLine3.innerText = 'Portal Siaga';
                    oledStatus.innerText = 'STANDBY';
                    oledStatus.style.color = '#10b981';
                }, 5000);

            } else {
                // ==================== PROTEKSI: AKSES DITOLAK ====================
                playBuzzerTone('error');

                // Palang Tetap Rapat Tertutup
                arm.style.transform = 'rotate(0deg)';
                lightRed.style.background = '#ef4444';
                lightRed.style.boxShadow = '0 0 15px #ef4444';
                lightGreen.style.background = '#064e3b';
                lightGreen.style.boxShadow = 'none';

                // Tampilan OLED Mini
                oledLine1.innerText = data.display?.line1 || 'PORTAL PERUMAHAN';
                oledLine2.innerText = data.display?.line2 || 'AKSES DITOLAK!';
                oledLine3.innerText = data.display?.line3 || 'KTP BLUM DAFTAR';
                oledStatus.innerText = 'DENIED';
                oledStatus.style.color = '#ef4444';

                // Banner Feedback Peringatan
                feedback.className = 'alert alert-error';
                feedback.style.background = 'rgba(239, 68, 68, 0.15)';
                feedback.style.border = '1px solid #ef4444';
                feedback.style.color = '#f87171';
                feedback.innerHTML = `
                    <div style="display:flex; align-items:center; gap:10px;">
                        <i class="fa-solid fa-triangle-exclamation" style="font-size:22px;"></i>
                        <div>
                            <strong>AKSES DITOLAK! Palang Pintu Tetap Terkunci.</strong><br>
                            ${data.message || 'Kartu e-KTP ini belum terdaftar di database sistem SIKAS-RT. Akses portal perumahan ditolak demi keamanan.'}
                        </div>
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error('Error simulating portal tap:', err);
            alert('Gagal menghubungi server simulator portal.');
        });
    }

    // =========================================================================
    // 2. SCAN VIA SMARTPHONE DENGAN WEB NFC API (CHROME ANDROID)
    // =========================================================================
    async function startWebNfcScan() {
        const statusBox = document.getElementById('webNfcStatus');
        statusBox.style.display = 'block';

        if (!('NDEFReader' in window)) {
            statusBox.style.background = 'rgba(245, 158, 11, 0.15)';
            statusBox.style.border = '1px solid #f59e0b';
            statusBox.style.color = '#fbbf24';
            statusBox.innerHTML = `
                <i class="fa-solid fa-circle-info"></i> <strong>Web NFC tidak didukung di browser ini.</strong><br>
                Fitur ini membutuhkan smartphone Android dengan NFC dan Google Chrome. Untuk saat ini, Anda bisa menggunakan <strong>Metode 2 (Scan di ESP32)</strong> atau <strong>Metode 3 (Input Manual)</strong> di bawah.
            `;
            return;
        }

        try {
            statusBox.style.background = 'rgba(6, 182, 212, 0.15)';
            statusBox.style.border = '1px solid #06b6d4';
            statusBox.style.color = '#38bdf8';
            statusBox.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin"></i> <strong>NFC Siaga!</strong> Tempelkan fisik e-KTP Anda ke bodi belakang smartphone sekarang...
            `;

            const ndef = new NDEFReader();
            await ndef.scan();

            ndef.onreading = event => {
                const serial = event.serialNumber; // misal: "04:5a:32:89:a0"
                if (serial) {
                    playBuzzerTone('pair');
                    statusBox.style.background = 'rgba(16, 185, 129, 0.15)';
                    statusBox.style.border = '1px solid #10b981';
                    statusBox.style.color = '#34d399';
                    statusBox.innerHTML = `<i class="fa-solid fa-circle-check"></i> Chip Terdeteksi: <strong>${serial}</strong>. Mendaftarkan ke akun...`;

                    // Submit ke backend
                    fetch('{{ route("ktp.register") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ rfid_uid: serial })
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            alert('e-KTP berhasil didaftarkan untuk akun Anda!');
                            window.location.reload();
                        } else {
                            alert(res.message || 'Gagal mendaftarkan KTP');
                        }
                    });
                }
            };

            ndef.onreadingerror = () => {
                statusBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Gagal membaca chip KTP. Pastikan posisi chip KTP pas di sensor NFC HP Anda.';
            };

        } catch (error) {
            statusBox.style.background = 'rgba(239, 68, 68, 0.15)';
            statusBox.style.border = '1px solid #ef4444';
            statusBox.style.color = '#f87171';
            statusBox.innerHTML = '<i class="fa-solid fa-xmark"></i> Izin NFC ditolak atau NFC HP belum diaktifkan: ' + error.message;
        }
    }

    // =========================================================================
    // 3. SCAN LIVE PAIRING VIA ESP32 HARDWARE READER
    // =========================================================================
    let pairingInterval = null;
    let pairingTimerSeconds = 60;
    let countdownInterval = null;

    function startEsp32Pairing() {
        const box = document.getElementById('esp32PairingBox');
        const btn = document.getElementById('btnEsp32Pairing');
        const timer = document.getElementById('pairingTimer');

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghubungkan...';

        fetch('{{ route("ktp.pairing.start") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            box.style.display = 'block';
            btn.innerHTML = '<i class="fa-solid fa-satellite-dish"></i> Mode Pairing Aktif';
            pairingTimerSeconds = data.expires_in || 60;
            timer.innerText = pairingTimerSeconds + 's';

            // Mulai hitung mundur
            clearInterval(countdownInterval);
            countdownInterval = setInterval(() => {
                pairingTimerSeconds--;
                timer.innerText = pairingTimerSeconds + 's';
                if (pairingTimerSeconds <= 0) {
                    cancelEsp32Pairing('Waktu pendaftaran KTP di ESP32 telah habis.');
                }
            }, 1000);

            // Polling status pairing dari ESP32 setiap 1.5 detik
            clearInterval(pairingInterval);
            pairingInterval = setInterval(() => {
                fetch('{{ route("ktp.pairing.status") }}')
                .then(r => r.json())
                .then(st => {
                    if (st.status === 'success') {
                        clearInterval(pairingInterval);
                        clearInterval(countdownInterval);
                        playBuzzerTone('pair');
                        alert('🎉 Berhasil! ' + st.message + '\nUID e-KTP: ' + st.uid);
                        window.location.reload();
                    } else if (st.status === 'expired') {
                        cancelEsp32Pairing('Sesi pairing telah berakhir.');
                    }
                })
                .catch(err => console.log('Polling error:', err));
            }, 1500);
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-satellite-dish"></i> Scan di ESP32';
            alert('Gagal mengaktifkan mode pairing di server.');
        });
    }

    function cancelEsp32Pairing(msg) {
        clearInterval(pairingInterval);
        clearInterval(countdownInterval);
        document.getElementById('esp32PairingBox').style.display = 'none';
        const btn = document.getElementById('btnEsp32Pairing');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-satellite-dish"></i> Scan di ESP32';

        fetch('{{ route("ktp.pairing.cancel") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        if (msg) {
            alert(msg);
        }
    }

    function openScanModal() {
        const input = document.getElementById('manual_rfid_uid');
        if (input) {
            input.focus();
            input.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
</script>
@endsection

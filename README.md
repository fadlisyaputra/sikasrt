# SIKAS-RT: Sistem Informasi Kas & Lingkungan RT Terpadu Berbasis IoT & ESP32

Aplikasi Web Modern untuk Tata Kelola Keuangan Kas RT dan Keamanan Lingkungan Cerdas (Smart Neighborhood) yang terintegrasi langsung dengan mikrokontroler **ESP32**, pembaca **e-KTP / RFID NFC**, **Palang Gerbang Otomatis**, **Smart Kotak Kas**, **Sistem Ronda Siskamling**, **Layanan Posyandu**, dan **Tagihan Warga**.

---

## 🌟 Fitur Utama

### 1. Multi-Role & Hak Akses Berjenjang
- **Superadmin**: Akses menyeluruh ke seluruh modul, konfigurasi gateway IoT ESP32, direktori warga, manajemen user, dan audit sistem.
- **Bendahara RT**: Pengelolaan master jenis iuran, verifikasi pembayaran, pencatatan kas RT, penerbitan tagihan serentak (*batch*), cetak faktur invoice/kuitansi, serta laporan neraca.
- **Ketua RT**: Dashboard monitoring eksekutif, rekap kepatuhan iuran, jadwal ronda & pemantauan pos siskamling, log portal gerbang, dan pengumuman warga.
- **Warga**: Portal bayar iuran, transparansi buku kas, pendaftaran kartu e-KTP, jadwal siskamling & check-in ronda, agenda posyandu, dan riwayat tagihan.

### 2. Integrasi IoT & ESP32 Terkoneksi
- **Otomasi Palang Gerbang Portal (e-KTP / RFID Access)**: Warga cukup menempelkan e-KTP / kartu RFID pada modul RC522. Relay palang gerbang otomatis membuka akses jika e-KTP sudah terdaftar.
- **Proteksi Akses e-KTP**: Jika e-KTP belum terdaftar atau berstatus diblokir, akses ditolak (*relay OFF, buzzer alarm error berbunyi, dan layar menampilkan penolakan*).
- **Mode Registrasi e-KTP Langsung**: Warga dapat mendaftarkan e-KTP mereka langsung melalui menu web (Web NFC, pairing langsung via ESP32, atau input UID).
- **Tap-to-Pay Iuran Kas RT**: Warga dapat membayar iuran secara instan di Pos Keamanan via tap kartu RFID.
- **Smart Kotak Kas & Infaq**: Deteksi setoran uang fisik melalui sensor, langsung tercatat otomatis di Buku Kas RT.
- **Tombol Darurat (Panic Button & Sirene)**: Tombol panik fisik di pos ronda atau digital di web memicu sirene hardware dan notifikasi visual waspada seketika di dashboard.
- **Simulator Hardware Terintegrasi**: Pengujian interaktif seluruh fitur ESP32 langsung melalui browser web tanpa harus menunggu perakitan fisik.

### 3. Fitur Sosial & Pelayanan Warga
- **Jadwal Ronda Siskamling (`/ronda`)**: Pengelompokan regu ronda harian (Senin - Minggu), penugasan warga, dan fitur check-in mandiri kehadiran ronda malam.
- **Layanan Posyandu (`/posyandu`)**: Agenda kegiatan berkala serta pencatatan rekam medis pemeriksaan balita (BB, TB, gizi, imunisasi) dan lansia (tensi, gula darah).
- **Tagihan Warga (`/tagihan`)**: Penerbitan invoice bulanan secara massal/individual, monitoring status bayar, serta cetak bukti kuitansi resmi.

---

## 👥 Kredensial Demo Akun

Aplikasi dilengkapi tombol **Quick Switcher** di bagian atas layar untuk berpindah akun secara instan tanpa perlu ketik ulang kata sandi:

| Role | Email Login | Password | Keterangan & Kartu RFID |
|------|-------------|----------|-------------------------|
| **Superadmin** | `admin@kasrt.local` | `password` | Akses sistem & IoT penuh |
| **Bendahara** | `bendahara@kasrt.local` | `password` | Hj. Ratna (Bendahara RT) |
| **Ketua RT** | `rt@kasrt.local` | `password` | Bpk. H. Bambang (Ketua RT) |
| **Warga 1** | `budi@kasrt.local` | `password` | Budi Santoso (`93:B4:72:A1`) |
| **Warga 2** | `siti@kasrt.local` | `password` | Siti Nurhaliza (`4A:8C:31:E9`) |
| **Warga 3** | `joko@kasrt.local` | `password` | Joko Widodo (`F2:1D:6E:B8`) |

---

## 🚀 Cara Menjalankan Aplikasi

1. **Clone repository & masuk ke direktori:**
   ```bash
   git clone https://github.com/fadlisyaputra/sikasrt.git
   cd sikasrt
   ```

2. **Salin file konfigurasi environment:**
   ```bash
   cp .env.example .env
   ```

3. **Install dependensi & generate app key:**
   ```bash
   composer install
   php artisan key:generate
   ```

4. **Jalankan Database Migration & Seeder:**
   ```bash
   php artisan migrate --seed
   ```

5. **Jalankan Web Server Laravel:**
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

6. **Akses Aplikasi melalui Browser:**
   Buka `http://127.0.0.1:8000` di browser Anda.

---

## 🔌 Direktori Firmware ESP32
Seluruh kode program mikrokontroler dan panduan wiring terletak pada:
- **Source Code Arduino (.ino):** `firmware_esp32/sikasrt_esp32.ino`
- **Panduan Pengkabelan & Pinout:** `firmware_esp32/README_ESP32.md`

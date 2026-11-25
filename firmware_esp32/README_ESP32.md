# Panduan Integrasi Hardware ESP32 & IoT SIKAS-RT
## Sistem Otomasi Portal Perumahan Berbasis e-KTP (RFID / NFC)

Aplikasi Web **SIKAS-RT** (Sistem Informasi Kas & Lingkungan RT Terpadu) mendukung penuh integrasi mikrokontroler **ESP32** dengan modul **RFID/NFC (MFRC522 / PN532)** untuk membuka palang portal perumahan menggunakan **e-KTP (KTP Elektronik Indonesia)** yang telah terdaftar pada akun warga yang bersangkutan.

---

## 1. Mengenal Chip RFID/NFC pada e-KTP Indonesia
- e-KTP Indonesia memuat chip nirkabel berstandar internasional **ISO/IEC 14443 Type A** dengan frekuensi kerja **13.56 MHz**.
- Setiap fisik e-KTP memiliki nomor identifikasi unik permanen yang disebut **UID (Unique Identifier)** (umumnya 4-byte atau 7-byte heksadesimal, misal: `93:B4:72:A1`).
- Modul pembaca RFID seperti **MFRC522** dan **PN532** dapat membaca UID ini secara instan saat e-KTP didekatkan dengan jarak 1–4 cm.

---

## 2. Alur Kerja Fitur (User Flow & Proteksi Keamanan)

### A. Alur Pendaftaran e-KTP oleh User yang Login
1. Warga / Pengurus RT melakukan login ke akun mereka di web SIKAS-RT.
2. Buka menu **e-KTP & Akses Portal** pada bilah navigasi kiri.
3. Tersedia 3 opsi praktis untuk mendaftarkan e-KTP:
   - **Metode 1: Scan via Smartphone (Web NFC API)**  
     Buka web di Google Chrome pada smartphone Android yang memiliki fitur NFC, klik tombol *Scan NFC HP*, lalu tempelkan e-KTP ke punggung ponsel. Browser akan membaca UID dan otomatis mendaftarkannya ke akun user.
   - **Metode 2: Scan Langsung via Alat ESP32 (Live Pairing Radar)**  
     Klik tombol *Scan di ESP32*. Web akan mengaktifkan mode pairing selama 60–90 detik. Warga cukup menempelkan e-KTP ke reader ESP32 di Pos RT / Portal. Server mendeteksi tap dan otomatis mem-pairing UID tersebut ke user yang sedang login!
   - **Metode 3: Input Manual UID KTP**  
     Masukkan kode heksadesimal UID e-KTP (misal format `93:B4:72:A1`) lalu klik Simpan.
4. Begitu terdaftar, status kartu pada akun user berubah menjadi **"TERDAFTAR & AKSES PORTAL AKTIF"**.

### B. Alur Pembukaan Portal Perumahan Saat e-KTP di-Scan
1. Warga tiba di gerbang portal perumahan dan menempelkan e-KTP ke reader RFID/NFC ESP32.
2. ESP32 membaca UID dan mengirim data JSON ke backend SIKAS-RT:
   ```json
   POST /api/esp32/rfid-tap
   Header: X-ESP32-KEY: esp_gate_rt01_key_889911
   Body: {"device_id": "ESP32-GATE-01", "rfid_uid": "93:B4:72:A1"}
   ```
3. **Verifikasi Server & Proteksi Keamanan**:
   - **KASUS 1: KTP TERDAFTAR & AKTIF**  
     Server merespons `authorized: true`, `action: "open_gate"`.  
     ESP32 mengaktifkan relay palang gerbang selama 5 detik, menyalakan buzzer bip 1x nada ceria, dan menampilkan teks di layar OLED:  
     `PORTAL TERBUKA - [NAMA WARGA] - SILAKAN MASUK`.  
     Server mencatat aktivitas ke tabel log telemetri portal.
   - **KASUS 2: KTP BELUM TERDAFTAR (PROTEKSI KEAMANAN DITOLAK)**  
     Server merespons `authorized: false`, `status: "denied"`.  
     ESP32 **TIDAK MENGAKTIFKAN RELAY** (palang gerbang tetap tertutup rapat/terkunci).  
     Buzzer membunyikan alarm peringatan 3x bip pendek (`TIT-TIT-TIT`), OLED menampilkan:  
     `AKSES DITOLAK! KTP Belum Terdaftar`.  
     Server mencatat insiden penolakan ke log telemetri keamanan untuk audit satpam RT.
   - **KASUS 3: KTP DIBLOKIR / KARTU HILANG**  
     Jika status kartu diblokir oleh pengurus, akses juga ditolak dengan pesan `KARTU DIBLOKIR`.

---

## 3. Komponen Hardware yang Digunakan
1. **ESP32 Development Board** (NodeMCU ESP-32S / ESP-WROOM-32)
2. **Modul RFID RC522 (SPI)** atau **PN532 (I2C/SPI)**
3. **Modul Relay 1-Channel 5V (Active LOW)** terhubung ke Solenoid Door Lock / Palang Pintu Otomatis
4. **Layar OLED 0.96 inch SSD1306 (I2C)** untuk status visual
5. **Active Buzzer 5V** untuk audio feedback
6. **Breadboard & Kabel Jumper secukupnya**

---

## 4. Skema Pengkabelan (Wiring Pinout)

### A. Modul RFID RC522 (Koneksi SPI ke ESP32)
| Pin RC522 | Pin ESP32 | Keterangan |
|---|---|---|
| **SDA (SS)** | **GPIO 5** | Chip Select |
| **SCK** | **GPIO 18** | SPI Clock |
| **MOSI** | **GPIO 23** | SPI Master Out |
| **MISO** | **GPIO 19** | SPI Master In |
| **RST** | **GPIO 4** | Reset Pin (Bebas dari SCL) |
| **GND** | **GND** | Ground |
| **3.3V** | **3.3V** | *Wajib 3.3V (Jangan ke 5V!)* |

### B. Layar OLED SSD1306 (Koneksi I2C ke ESP32)
| Pin OLED | Pin ESP32 | Keterangan |
|---|---|---|
| **SDA** | **GPIO 21** | I2C Data |
| **SCL** | **GPIO 22** | I2C Clock |
| **VCC** | **3.3V / 5V** | Tegangan Operasi |
| **GND** | **GND** | Ground |

### C. Relay & Buzzer
| Komponen | Pin ESP32 | Keterangan |
|---|---|---|
| **IN Relay Palang** | **GPIO 26** | LOW = Palang Buka, HIGH = Palang Tutup |
| **VCC Relay** | **5V (VIN)** | Power Relay |
| **GND Relay** | **GND** | Ground Bersama |
| **Buzzer (+)** | **GPIO 27** | Nada feedback |
| **Buzzer (-)** | **GND** | Ground |

---

## 5. Library Arduino yang Diperlukan
Di Arduino IDE, buka menu **Tools > Manage Libraries...**, lalu pasang:
1. **MFRC522** by GithubCommunity
2. **Adafruit SSD1306** by Adafruit
3. **Adafruit GFX Library** by Adafruit
4. **ArduinoJson** by Benoit Blanchon (Versi 6.x atau 7.x)

---

## 6. Konfigurasi & Upload Firmware
1. Buka file `firmware_esp32/sikasrt_esp32.ino` di Arduino IDE.
2. Sesuaikan konfigurasi WiFi dan IP Server Anda pada baris atas:
   ```cpp
   const char* WIFI_SSID       = "NAMA_WIFI_ANDA";
   const char* WIFI_PASSWORD   = "PASSWORD_WIFI_ANDA";
   const char* SERVER_BASE_URL = "http://192.168.1.100:8000"; // Ganti dengan IP lokal laptop/server
   const char* DEVICE_ID       = "ESP32-GATE-01";
   const char* API_KEY         = "esp_gate_rt01_key_889911";
   ```
3. Pilih board **ESP32 Dev Module** dan Port COM yang sesuai.
4. Klik **Upload**.
5. Buka **Serial Monitor** pada kecepatan baud **115200** untuk melihat log pembacaan e-KTP dan status respon dari server.

---

## 7. Pengujian Simulator di Web
Aplikasi web SIKAS-RT telah dilengkapi **Simulator Palang Pintu Portal & Scanner e-KTP Real-Time** di:
`http://localhost:8000/ktp`
- Anda dapat menguji membuka gerbang dengan e-KTP terdaftar akun Anda.
- Anda dapat menguji proteksi keamanan dengan kartu asing yang belum terdaftar untuk melihat palang tetap terkunci dan alarm berbunyi.

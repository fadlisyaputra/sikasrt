/*
 * ==============================================================================
 * SIKAS-RT: Sistem Informasi Kas & Lingkungan RT Terpadu Berbasis IoT ESP32
 * Firmware ESP32 Node Gateway & Smart Pos / Portal RT
 * ==============================================================================
 * Fitur Firmware:
 * 1. Otomasi Gerbang Portal RT via Kartu RFID Warga (MFRC522)
 * 2. Tap-to-Pay Iuran Kas RT di Pos Ronda
 * 3. Smart Kotak Kas / Infaq (Deteksi Drop Uang via IR/Switch Sensor)
 * 4. Tombol Darurat Siskamling (Panic Button & Sirene Keamanan)
 * 5. Tampilan Layar OLED SSD1306 (128x64 I2C) Interaktif
 * 6. Komunikasi REST API Dua Arah (JSON Heartbeat, Polling & Kontrol Relay Jarak Jauh)
 * ==============================================================================
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>

// ===================== KONFIGURASI WIFI & SERVER =====================
const char* WIFI_SSID     = "NAMA_WIFI_ANDA";        // Masukkan nama WiFi Anda
const char* WIFI_PASSWORD = "PASSWORD_WIFI_ANDA";    // Masukkan password WiFi Anda

// Alamat URL Server SIKAS-RT (IP Komputer Anda yang menjalankan Laravel)
const char* SERVER_BASE_URL = "http://sikas.mehape.com:2214"; // URL Server Online VPS (atau http://77.216.8.44:2214) 
const char* DEVICE_ID       = "ESP32-GATE-01";             // ID Perangkat terdaftar
const char* API_KEY         = "esp_gate_rt01_key_889911";  // Kunci API Perangkat

// ===================== DEFINISI PIN HARDWARE =====================
// RFID RC522 (Koneksi Bus SPI Standar ESP32)
#define PIN_RFID_SS     5    // Pin SDA / SS pada RC522
#define PIN_RFID_RST    4    // Pin RST pada RC522 (Gunakan GPIO 4 agar tidak bentrok dengan SCL)
// Catatan SPI RC522: SCK = GPIO 18, MISO = GPIO 19, MOSI = GPIO 23

// Layar OLED SSD1306 (Koneksi Bus I2C Standar ESP32: SDA=GPIO 21, SCL=GPIO 22)
#define SCREEN_WIDTH    128
#define SCREEN_HEIGHT   64
#define OLED_RESET      -1
#define SCREEN_ADDRESS  0x3C

// Aktuator & Sensor
#define PIN_RELAY_GATE  26   // Relay Palang Pintu / Solenoid (Active LOW: LOW=Buka, HIGH=Tutup)
#define PIN_BUZZER      27   // Active Buzzer 5V (HIGH=Bunyi)
#define PIN_LED_STATUS  2    // Built-in Blue LED Indikator WiFi
#define PIN_PANIC_BTN   13   // Tombol Darurat Siskamling (Opsional)
#define PIN_KAS_DROP    14   // Sensor Deteksi Drop Uang Kas (Opsional)

// Objek Hardware
MFRC522 rfid(PIN_RFID_SS, PIN_RFID_RST);
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

// Variabel Waktu (Non-blocking)
unsigned long lastHeartbeatTime = 0;
const unsigned long HEARTBEAT_INTERVAL = 10000; // 10 detik

unsigned long gateOpenTimer = 0;
bool isGateOpen = false;
const unsigned long GATE_DURATION = 5000; // Gerbang terbuka selama 5 detik

// Debounce Anti-Spam Pembacaan Kartu / e-KTP
String lastCardUid = "";
unsigned long lastCardTapTime = 0;
const unsigned long CARD_TAP_COOLDOWN = 3000; // Cooldown 3 detik per kartu

bool isEmergencyActive = false;

// ===================== SETUP UTAMA =====================
void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println("\n[SIKAS-RT IoT] Booting ESP32 System...");

  // Inisialisasi Pin I/O
  pinMode(PIN_RELAY_GATE, OUTPUT);
  pinMode(PIN_BUZZER, OUTPUT);
  pinMode(PIN_LED_STATUS, OUTPUT);
  
  pinMode(PIN_PANIC_BTN, INPUT_PULLUP);
  pinMode(PIN_KAS_DROP, INPUT_PULLUP);

  // Default state: Gerbang Tertutup, Buzzer Diam
  digitalWrite(PIN_RELAY_GATE, HIGH); // Relay active LOW (HIGH = MATI)
  digitalWrite(PIN_BUZZER, LOW);
  digitalWrite(PIN_LED_STATUS, LOW);

  // Inisialisasi Bus SPI & RFID
  SPI.begin();
  rfid.PCD_Init();

  // Inisialisasi Layar OLED
  if (!display.begin(SSD1306_SWITCHCAPVCC, SCREEN_ADDRESS)) {
    Serial.println("[ERROR] OLED SSD1306 tidak terdeteksi!");
  } else {
    display.clearDisplay();
    display.setTextColor(SSD1306_WHITE);
    display.setTextSize(1);
    display.setCursor(0, 10);
    display.println("  SIKAS-RT IoT");
    display.println(" Smart RT 01/RW 05");
    display.println(" Connecting WiFi...");
    display.display();
  }

  // Koneksi WiFi
  connectToWiFi();

  beepBuzzer(100, 2); // 2x beep tanda siap
  updateOledStandby();
}

// ===================== LOOP UTAMA =====================
void loop() {
  // 1. Cek Koneksi WiFi
  if (WiFi.status() != WL_CONNECTED) {
    digitalWrite(PIN_LED_STATUS, LOW);
    connectToWiFi();
  } else {
    digitalWrite(PIN_LED_STATUS, HIGH);
  }

  // 2. Cek Timer Penutupan Gerbang Otomatis
  if (isGateOpen && (millis() - gateOpenTimer >= GATE_DURATION)) {
    closeGate();
  }

  // 3. Cek Tap Kartu RFID Baru
  checkRfidCard();

  // 4. Cek Tombol Darurat / Panic Button Fisik
  checkPanicButton();

  // 5. Cek Drop Uang Fisik ke Smart Kotak Kas
  checkKasDropSensor();

  // 6. Pengiriman Heartbeat Berkala & Penerimaan Perintah Remote
  if (millis() - lastHeartbeatTime >= HEARTBEAT_INTERVAL) {
    lastHeartbeatTime = millis();
    sendHeartbeat();
  }
}

// ===================== FUNGSI HARDWARE & LOGIKA =====================

void connectToWiFi() {
  Serial.print("[WIFI] Menghubungkan ke ");
  Serial.println(WIFI_SSID);

  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n[WIFI] Terhubung!");
    Serial.print("[WIFI] IP Address: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\n[WIFI] Gagal terhubung! Berjalan dalam mode offline.");
  }
}

void updateOledStandby() {
  display.clearDisplay();
  display.setTextSize(1);
  display.setCursor(0, 0);
  display.println("=== SIKAS-RT IOT ===");
  display.setCursor(0, 18);
  display.println("Status: STANDBY");
  display.setCursor(0, 32);
  display.println("SILAKAN TEMPELKAN");
  display.println("KARTU RFID WARGA...");
  display.setCursor(0, 54);
  display.print("IP: ");
  display.println(WiFi.localIP());
  display.display();
}

void openGate(int durationMs, String line1, String line2, String line3) {
  isGateOpen = true;
  gateOpenTimer = millis();
  digitalWrite(PIN_RELAY_GATE, LOW); // Aktifkan relay palang pintu

  beepBuzzer(150, 1);

  display.clearDisplay();
  display.setTextSize(1);
  display.setCursor(0, 0);
  display.println(">>> " + line1 + " <<<");
  display.setCursor(0, 20);
  display.setTextSize(2);
  display.println(line2.substring(0, 10));
  display.setTextSize(1);
  display.setCursor(0, 44);
  display.println(line3);
  display.setCursor(0, 56);
  display.println("Palang Terbuka...");
  display.display();

  Serial.println("[GATE] PALANG PINTU DIBUKA!");
}

void closeGate() {
  isGateOpen = false;
  digitalWrite(PIN_RELAY_GATE, HIGH); // Matikan relay
  Serial.println("[GATE] Palang pintu ditutup kembali.");
  updateOledStandby();
}

void beepBuzzer(int durationMs, int times) {
  for (int i = 0; i < times; i++) {
    digitalWrite(PIN_BUZZER, HIGH);
    delay(durationMs);
    digitalWrite(PIN_BUZZER, LOW);
    if (times > 1) delay(80);
  }
}

// ------------------- MEMBACA KARTU / e-KTP (RFID/NFC) -------------------
void checkRfidCard() {
  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) {
    return;
  }

  // Konversi UID ke format Hex String "XX:XX:XX:XX"
  String uidStr = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    if (rfid.uid.uidByte[i] < 0x10) uidStr += "0";
    uidStr += String(rfid.uid.uidByte[i], HEX);
    if (i < rfid.uid.size - 1) uidStr += ":";
  }
  uidStr.toUpperCase();

  // Debounce: Cegah request berulang jika kartu yang sama menempel terus
  if (uidStr == lastCardUid && (millis() - lastCardTapTime < CARD_TAP_COOLDOWN)) {
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    return;
  }
  lastCardUid = uidStr;
  lastCardTapTime = millis();

  Serial.println("\n[RFID/NFC] Kartu Ditempel! UID: " + uidStr);

  display.clearDisplay();
  display.setTextSize(1);
  display.setCursor(0, 10);
  display.println("MEMBACA e-KTP...");
  display.println("UID: " + uidStr);
  display.println("Verifikasi Server...");
  display.display();

  // Kirim data ke API SIKAS-RT
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    String endpoint = String(SERVER_BASE_URL) + "/api/esp32/rfid-tap";
    http.begin(endpoint);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-ESP32-KEY", API_KEY);

    StaticJsonDocument<256> doc;
    doc["device_id"] = DEVICE_ID;
    doc["rfid_uid"] = uidStr;

    String requestBody;
    serializeJson(doc, requestBody);

    int httpCode = http.POST(requestBody);
    if (httpCode == 200) {
      String response = http.getString();
      Serial.println("[API RESPONSE] " + response);

      StaticJsonDocument<512> resDoc;
      deserializeJson(resDoc, response);

      bool authorized = resDoc["authorized"] | false;
      String status = resDoc["status"] | "";
      String line1 = resDoc["display"]["line1"] | "BERHASIL";
      String line2 = resDoc["display"]["line2"] | "WARGA RT";
      String line3 = resDoc["display"]["line3"] | "";
      String action = resDoc["action"] | "";
      String msg = resDoc["message"] | "";

      if (authorized) {
        if (action == "open_gate") {
          // BUKA PALANG PINTU PORTAL
          openGate(5000, line1, line2, line3);
        } else if (status == "registered") {
          // MODE REGISTRASI / PAIRING e-KTP BERHASIL
          beepBuzzer(100, 2);
          display.clearDisplay();
          display.setTextSize(1);
          display.setCursor(0, 5);
          display.println(">>> REGISTRASI KTP <<<");
          display.setCursor(0, 22);
          display.setTextSize(2);
          display.println("SUKSES!");
          display.setTextSize(1);
          display.setCursor(0, 44);
          display.println(line2.substring(0, 16));
          display.setCursor(0, 56);
          display.println("KTP Berhasil Terdaftar");
          display.display();
          delay(3500);
          updateOledStandby();
        } else {
          beepBuzzer(100, 2);
          display.clearDisplay();
          display.setTextSize(1);
          display.setCursor(0, 10);
          display.println(line1);
          display.println(line2);
          display.println(line3);
          display.display();
          delay(3000);
          updateOledStandby();
        }
      } else {
        // =========================================================
        // PROTEKSI: KTP BELUM TERDAFTAR ATAU AKSES DITOLAK
        // =========================================================
        Serial.println("[PROTEKSI] Akses Ditolak! KTP belum terdaftar / diblokir.");
        
        // Palang gerbang TETAP MATI / TERTUTUP (Relay HIGH = OFF)
        digitalWrite(PIN_RELAY_GATE, HIGH);

        // Bunyikan Buzzer Peringatan 3x Bip Cepat
        for (int b = 0; b < 3; b++) {
          digitalWrite(PIN_BUZZER, HIGH);
          delay(120);
          digitalWrite(PIN_BUZZER, LOW);
          delay(80);
        }

        display.clearDisplay();
        display.setTextSize(1);
        display.setCursor(0, 5);
        display.println(">>> SIKAS-RT GATE <<<");
        display.setCursor(0, 20);
        display.setTextSize(2);
        display.println("DITOLAK!");
        display.setTextSize(1);
        display.setCursor(0, 42);
        display.println(line3.length() > 0 ? line3 : "KTP Belum Terdaftar");
        display.setCursor(0, 54);
        display.println(uidStr);
        display.display();
        delay(3000);
        updateOledStandby();
      }
    } else {
      Serial.print("[HTTP ERROR] Kode: ");
      Serial.println(httpCode);
    }
    http.end();
  }

  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();
}

// ------------------- DETEKSI TOMBOL PANIK -------------------
void checkPanicButton() {
  if (digitalRead(PIN_PANIC_BTN) == LOW) {
    delay(50); // Debounce
    if (digitalRead(PIN_PANIC_BTN) == LOW) {
      Serial.println("[PANIC] TOMBOL DARURAT DITEKAN!");

      // Bunyikan sirene seketika
      beepBuzzer(500, 3);

      display.clearDisplay();
      display.setCursor(0, 15);
      display.setTextSize(2);
      display.println("! BAHAYA !");
      display.setTextSize(1);
      display.println("Alarm Darurat Aktif!");
      display.display();

      if (WiFi.status() == WL_CONNECTED) {
        HTTPClient http;
        String endpoint = String(SERVER_BASE_URL) + "/api/esp32/panic-alert";
        http.begin(endpoint);
        http.addHeader("Content-Type", "application/json");

        StaticJsonDocument<256> doc;
        doc["device_id"] = DEVICE_ID;
        doc["alert_type"] = "panic_button";
        doc["description"] = "Tombol Darurat Pos Keamanan Fisik Ditekan";

        String req;
        serializeJson(doc, req);
        http.POST(req);
        http.end();
      }

      delay(3000);
      updateOledStandby();
    }
  }
}

// ------------------- DETEKSI DROP KOTAK KAS -------------------
void checkKasDropSensor() {
  if (digitalRead(PIN_KAS_DROP) == LOW) {
    delay(100); // Debounce
    if (digitalRead(PIN_KAS_DROP) == LOW) {
      Serial.println("[SMART KAS] Deteksi uang masuk kotak!");

      beepBuzzer(100, 1);

      if (WiFi.status() == WL_CONNECTED) {
        HTTPClient http;
        String endpoint = String(SERVER_BASE_URL) + "/api/esp32/kas-drop";
        http.begin(endpoint);
        http.addHeader("Content-Type", "application/json");

        StaticJsonDocument<256> doc;
        doc["device_id"] = "ESP32-KASBOX-01";
        doc["amount"] = 10000;

        String req;
        serializeJson(doc, req);
        int code = http.POST(req);
        if (code == 200) {
          String res = http.getString();
          StaticJsonDocument<256> resDoc;
          deserializeJson(resDoc, res);

          display.clearDisplay();
          display.setCursor(0, 10);
          display.println("KAS DITERIMA:");
          display.println("Rp 10.000");
          display.println("Terima Kasih!");
          display.display();
          delay(2500);
          updateOledStandby();
        }
        http.end();
      }
    }
  }
}

// ------------------- HEARTBEAT & REMOTE COMMAND -------------------
void sendHeartbeat() {
  if (WiFi.status() != WL_CONNECTED) return;

  HTTPClient http;
  String endpoint = String(SERVER_BASE_URL) + "/api/esp32/heartbeat";
  http.begin(endpoint);
  http.addHeader("Content-Type", "application/json");

  StaticJsonDocument<256> doc;
  doc["device_id"] = DEVICE_ID;
  doc["ip_address"] = WiFi.localIP().toString();
  doc["rssi"] = WiFi.RSSI();
  doc["firmware_version"] = "1.2.0-esp32-mfrc522";

  String req;
  serializeJson(doc, req);

  int httpCode = http.POST(req);
  if (httpCode == 200) {
    String res = http.getString();
    StaticJsonDocument<512> resDoc;
    deserializeJson(resDoc, res);

    // Cek apakah ada antrean perintah remote dari Web SIKAS-RT
    JsonArray commands = resDoc["commands"].as<JsonArray>();
    for (JsonObject cmd : commands) {
      String commandName = cmd["command"].as<String>();
      Serial.println("[REMOTE COMMAND DITERIMA] " + commandName);

      if (commandName == "open_gate") {
        openGate(5000, "REMOTE OPEN", "WEB DASHBOARD", "Pengurus RT");
      } else if (commandName == "trigger_siren") {
        beepBuzzer(300, 3);
      } else if (commandName == "buzzer_on") {
        digitalWrite(PIN_BUZZER, HIGH);
        delay(1000);
        digitalWrite(PIN_BUZZER, LOW);
      }
    }
  }
  http.end();
}

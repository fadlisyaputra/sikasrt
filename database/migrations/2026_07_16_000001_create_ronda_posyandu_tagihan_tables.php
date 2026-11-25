<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop if exists first to allow clean migration
        Schema::dropIfExists('warga_bills');
        Schema::dropIfExists('posyandu_records');
        Schema::dropIfExists('posyandu_activities');
        Schema::dropIfExists('ronda_attendances');
        Schema::dropIfExists('ronda_members');
        Schema::dropIfExists('ronda_schedules');

        // ==========================================
        // 1. TABEL JADWAL RONDA & SISKAMLING
        // ==========================================
        Schema::create('ronda_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('day_name'); // Senin, Selasa, Rabu, Kamis, Jumat, Sabtu, Minggu
            $table->string('group_name'); // Regu 1 (Garuda), Regu 2 (Rajawali), dll.
            $table->string('start_time')->default('22:00');
            $table->string('end_time')->default('04:00');
            $table->string('location')->default('Pos Ronda Utama RT 01');
            $table->foreignId('coordinator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ronda_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ronda_schedule_id')->constrained('ronda_schedules')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role_in_team')->default('Anggota'); // Koordinator, Anggota
            $table->timestamps();
        });

        Schema::create('ronda_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ronda_schedule_id')->nullable()->constrained('ronda_schedules')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('attendance_date');
            $table->enum('status', ['hadir', 'izin', 'alpha'])->default('hadir');
            $table->enum('method', ['rfid_ktp', 'manual_admin', 'qr_pos'])->default('manual_admin');
            $table->text('notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // ==========================================
        // 2. TABEL POSYANDU BALITA, LANSIA & WARGA
        // ==========================================
        Schema::create('posyandu_activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('category', ['balita', 'lansia', 'ibu_hamil', 'umum'])->default('balita');
            $table->date('activity_date');
            $table->string('start_time')->default('08:30');
            $table->string('end_time')->default('11:30');
            $table->string('location')->default('Balai Warga RT 01 / RW 05');
            $table->string('officer_name')->default('Bidan & Kader Posyandu Melati');
            $table->text('description')->nullable();
            $table->enum('status', ['upcoming', 'completed', 'cancelled'])->default('upcoming');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('posyandu_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posyandu_activity_id')->nullable()->constrained('posyandu_activities')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Keluarga / Warga
            $table->string('participant_name');
            $table->enum('participant_category', ['balita', 'lansia', 'ibu_hamil', 'umum'])->default('balita');
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->string('age_text')->nullable(); // misal: "2 Tahun", "65 Tahun"
            $table->decimal('weight', 5, 2)->nullable(); // BB kg
            $table->decimal('height', 5, 2)->nullable(); // TB cm
            $table->string('blood_pressure', 20)->nullable(); // Tensi (Lansia)
            $table->decimal('blood_sugar', 6, 1)->nullable(); // Gula Darah (mg/dL)
            $table->decimal('cholesterol', 6, 1)->nullable(); // Kolesterol (mg/dL)
            $table->string('vitamin_immunization')->nullable(); // Vaksin / Vitamin
            $table->string('health_status')->default('Sehat'); // Status Gizi / Kesehatan
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // ==========================================
        // 3. TABEL TAGIHAN WARGA (BILLING & INVOICING)
        // ==========================================
        Schema::create('warga_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_code')->unique(); // e.g., INV-202611-001
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('iuran_type_id')->constrained('iuran_types')->onDelete('cascade');
            $table->integer('period_month');
            $table->integer('period_year');
            $table->decimal('amount', 12, 2);
            $table->date('due_date');
            $table->enum('status', ['unpaid', 'pending', 'paid', 'cancelled'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('iuran_payment_id')->nullable()->constrained('iuran_payments')->onDelete('set null');
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warga_bills');
        Schema::dropIfExists('posyandu_records');
        Schema::dropIfExists('posyandu_activities');
        Schema::dropIfExists('ronda_attendances');
        Schema::dropIfExists('ronda_members');
        Schema::dropIfExists('ronda_schedules');
    }
};

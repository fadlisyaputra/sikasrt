<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Surat Menyurat (Surat Pengantar RT)
        Schema::dropIfExists('surat_requests');
        Schema::create('surat_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nomor_surat')->nullable();
            $table->string('jenis_surat'); // Surat Pengantar KTP/KK, Domisili, Keterangan Usaha, dll.
            $table->text('keperluan');
            $table->text('keterangan')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan_pengurus')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // 2. UMKM Warga (Etalase Produk Komunitas RT)
        Schema::dropIfExists('umkm_products');
        Schema::create('umkm_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nama_usaha');
            $table->string('nama_produk');
            $table->string('kategori'); // Makanan & Minuman, Jasa & Servis, Kerajinan, Fashion, Sembako
            $table->text('deskripsi');
            $table->decimal('harga', 12, 2);
            $table->string('satuan')->default('porsi'); // porsi, pcs, paket, unit
            $table->string('no_whatsapp');
            $table->string('alamat_usaha')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->boolean('is_verified')->default(true);
            $table->timestamps();
        });

        // 3. Bank Sampah RT (Kategori Sampah)
        Schema::dropIfExists('bank_sampah_transactions');
        Schema::dropIfExists('bank_sampah_categories');
        
        Schema::create('bank_sampah_categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori'); // Plastik Botol, Kardus, Kertas HVS, Logam/Besi, Minyak Jelantah
            $table->string('kode')->unique();
            $table->decimal('harga_per_kg', 10, 2);
            $table->string('satuan')->default('kg');
            $table->string('deskripsi')->nullable();
            $table->timestamps();
        });

        // Bank Sampah RT (Transaksi Setoran & Penarikan Saldo)
        Schema::create('bank_sampah_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('bank_sampah_categories')->nullOnDelete();
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('tipe', ['setor', 'tarik']); // setor sampah atau tarik tabungan
            $table->decimal('berat_kg', 8, 2)->default(0);
            $table->decimal('harga_satuan', 10, 2)->default(0);
            $table->decimal('total_rp', 12, 2);
            $table->string('status')->default('completed'); // completed, pending
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_sampah_transactions');
        Schema::dropIfExists('bank_sampah_categories');
        Schema::dropIfExists('umkm_products');
        Schema::dropIfExists('surat_requests');
    }
};

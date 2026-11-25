<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['pemasukan', 'pengeluaran']);
            $table->string('category'); // e.g. Iuran Warga, Kebersihan, Keamanan, Konsumsi, Maintenance
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->text('description');
            $table->string('receipt_file')->nullable();
            $table->foreignId('iuran_payment_id')->nullable()->constrained('iuran_payments')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};

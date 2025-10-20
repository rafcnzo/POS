<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payroll', function (Blueprint $table) {
            $table->id();

            $table->foreignId('karyawan_id')
                ->constrained('karyawans')
                ->onDelete('cascade');
            $table->integer('bulan');
            $table->integer('tahun');
            $table->integer('jumlah_absensi')->default(0);
            $table->decimal('nominal_gaji', 15, 2)->default(0);
            $table->enum('status_pembayaran', ['pending', 'dibayar'])->default('pending');
            $table->date('tanggal_pembayaran')->nullable();
            $table->timestamps();
            $table->unique(['karyawan_id', 'bulan', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll');
    }
};

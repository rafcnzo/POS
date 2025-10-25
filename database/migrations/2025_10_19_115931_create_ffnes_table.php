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
        Schema::create('ffnes', function (Blueprint $table) {
            $table->id();
            $table->string('kode_ffne')->unique();
            $table->string('nama_ffne');
            $table->enum('kategori_ffne', ['Barang Habis Pakai', 'Barang Tidak Habis Pakai']);
            $table->decimal('harga', 15, 2)->default(0);
            $table->string('satuan_ffne');
            $table->boolean('kondisi_ffne')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ffnes');
    }
};

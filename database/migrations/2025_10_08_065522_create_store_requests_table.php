<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('issued_by')->nullable()->comment('User yang mengeluarkan barang')->constrained('users');
            $table->timestamp('issued_at')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['proses', 'po'])->default('proses');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_requests');
    }
};

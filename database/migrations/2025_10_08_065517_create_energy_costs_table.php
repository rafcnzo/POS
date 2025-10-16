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
        Schema::create('energy_costs', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: Gas LPG 12kg, Listrik Dapur
            $table->decimal('cost', 15, 2);
            $table->date('period')->comment('Untuk periode bulan/tanggal berapa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_costs');
    }
};

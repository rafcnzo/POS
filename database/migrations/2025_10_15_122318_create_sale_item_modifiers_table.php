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
        Schema::create('sale_item_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_item_id')->constrained('sale_items')->onDelete('cascade');
            $table->foreignId('modifier_id')->constrained('modifiers');
            $table->decimal('price', 15, 2)->comment('Harga modifier pada saat transaksi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_item_modifiers');
    }
};

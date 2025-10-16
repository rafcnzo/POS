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
        Schema::create('modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modifier_group_id')->constrained('modifier_groups')->onDelete('cascade');
            $table->string('name'); // Contoh: "Telur Dadar", "Extra Shot"
            $table->decimal('price', 15, 2)->default(0); // Harga tambahan
        
            // PENTING: Untuk mengurangi stok bahan baku
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredients');
            $table->decimal('quantity_used', 10, 2)->default(1); // Jumlah bahan yang terpakai
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modifiers');
    }
};

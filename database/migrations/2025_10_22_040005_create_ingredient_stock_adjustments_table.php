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
        Schema::create('ingredient_stock_adjustments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('ingredient_id')->constrained('ingredients')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->comment('User yang melakukan opname/adj');
            
            $table->enum('type', ['opname', 'waste', 'manual_add', 'initial', 'pembelian', 'penjualan']);
            $table->decimal('quantity', 10, 2); 
            $table->decimal('stock_before', 10, 2);
            $table->decimal('stock_after', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps(); // Ini akan jadi 'adjustment_date'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_stock_adjustments');
    }
};

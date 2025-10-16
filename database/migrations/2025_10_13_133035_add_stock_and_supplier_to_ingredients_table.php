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
        Schema::table('ingredients', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->decimal('minimum_stock', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            //
        });
    }
};

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
        Schema::create('store_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_request_id')->constrained('store_requests')->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained('ingredients');
            $table->decimal('requested_quantity', 10, 2);
            $table->decimal('issued_quantity', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_request_items');
    }
};

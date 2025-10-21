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
        Schema::create('ffne_stock_adjs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ffne_id')->constrained('ffnes')->onDelete('cascade');
            $table->integer('qty');
            $table->enum('type', ['initial', 'usage', 'received', 'waste', 'adjustment'])
                  ->default('usage')
                  ->after('qty');
            $table->string('notes')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable()->after('notes'); 
            $table->string('reference_type')->nullable()->after('reference_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ffne_stock_adjs');
    }
};

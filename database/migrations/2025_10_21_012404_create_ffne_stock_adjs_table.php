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
            $table->bigIncrements('id');

            $table->foreignId('ffne_id')
                ->constrained('ffnes')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->decimal('quantity', 10, 2);
            $table->decimal('stock_before', 10, 2)->default(0);
            $table->decimal('stock_after', 10, 2)->default(0);

            $table->enum('type', ['opname', 'waste', 'manual_add', 'initial', 'pembelian', 'penjualan'])
                ->default('initial');

            $table->string('notes')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable();

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

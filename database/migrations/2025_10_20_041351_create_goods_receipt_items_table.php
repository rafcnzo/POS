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
        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')
                    ->constrained('goods_receipts')
                    ->onDelete('cascade')
                    ->comment('ID dari goods receipt header');
            $table->foreignId('purchase_order_item_id')
                    ->constrained('purchase_order_items')
                    ->onDelete('cascade')
                    ->comment('ID dari purchase order item');
            $table->decimal('quantity_received', 10, 2)
                    ->comment('Jumlah barang yang diterima dalam kondisi baik');
            $table->decimal('quantity_rejected', 10, 2)
                    ->default(0)
                    ->comment('Jumlah barang yang ditolak karena rusak/cacat');
            $table->text('notes')
                    ->nullable()
                    ->comment('Catatan khusus untuk item ini, misal: alasan reject, kondisi barang, dll');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_items');
    }
};

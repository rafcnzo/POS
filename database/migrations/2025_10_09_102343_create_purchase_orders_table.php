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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique()->comment('Nomor PO, misal: PO-202510-001');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('user_id')->comment('User yang membuat PO (Accounting)')->constrained('users');
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['diproses', 'diterima'])->default('diproses');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};

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
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('deposit_payment_method')
                ->nullable()               // Boleh kosong jika deposit 0
                ->after('deposit_amount'); // Letakkan setelah kolom deposit_amount
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Hapus kolom jika di-rollback
            $table->dropColumn('deposit_payment_method');
        });
    }
};

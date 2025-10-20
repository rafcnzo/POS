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
        Schema::table('ffnes', function (Blueprint $table) {
            $table->decimal('qty', 10, 2)->default(0)->after('satuan_ffne')->comment('Jumlah stok FFNE yang tersedia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ffnes', function (Blueprint $table) {
            // Hapus kolom jika di-rollback
            $table->dropColumn('qty');
        });
    }
};

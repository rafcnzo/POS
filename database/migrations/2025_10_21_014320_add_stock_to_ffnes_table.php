<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ffnes', function (Blueprint $table) {
            $table->integer('stock')->default(0)->after('satuan_ffne');
            $table->dropForeign(['extra_id']); // Hapus jika constraint sudah ada
            $table->dropColumn('extra_id'); // Hapus kolom ini
        });
    }

    public function down(): void
    {
        Schema::table('ffnes', function (Blueprint $table) {
            $table->dropColumn('stock');
            $table->foreignId('extra_id')->nullable()->constrained('extras')->onDelete('set null'); // Kembalikan jika rollback
        });
    }
};

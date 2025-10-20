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
        Schema::table('karyawans', function (Blueprint $table) {
            $table->text('alamat')->nullable()->after('position');
            $table->string('no_hp', 20)->nullable()->after('alamat');
            $table->string('kontak_darurat', 100)->nullable()->after('no_hp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn(['alamat', 'no_hp', 'kontak_darurat']);
        });
    }
};

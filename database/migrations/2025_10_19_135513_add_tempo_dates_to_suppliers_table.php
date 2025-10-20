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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->date('jatuh_tempo1')->nullable()->after('credit_limit'); 
            $table->date('jatuh_tempo2')->nullable()->after('jatuh_tempo1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['jatuh_tempo1', 'jatuh_tempo2']);
        });
    }
};

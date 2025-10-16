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
        Schema::table('sales', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('user_id');
            $table->string('table_number')->nullable()->after('customer_name');
            $table->enum('order_type', ['dine_in', 'take_away'])->default('dine_in')->after('table_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            //
        });
    }
};

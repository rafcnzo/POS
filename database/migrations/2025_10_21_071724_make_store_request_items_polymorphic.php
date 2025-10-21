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
        Schema::table('store_request_items', function (Blueprint $table) {
            $table->dropForeign(['ingredient_id']); 
            $table->dropColumn('ingredient_id');
            $table->morphs('itemable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_request_items', function (Blueprint $table) {
            $table->dropMorphs('itemable');
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredients');
        });
    }
};

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
        Schema::create('menu_item_modifier_group', function (Blueprint $table) {
            $table->string('menu_item_id');
            $table->foreign('menu_item_id')->references('id')->on('menu_items')->onDelete('cascade');
        
            $table->foreignId('modifier_group_id')->constrained('modifier_groups')->onDelete('cascade');
        
            $table->primary(['menu_item_id', 'modifier_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_item_modifier_group');
    }
};

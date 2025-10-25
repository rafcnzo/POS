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
        Schema::create('reservations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('customer_name');
            $table->string('table_number')->nullable();                                                              
            $table->integer('pax');                                                                                    
            $table->dateTime('reservation_time');                                                                      
            $table->decimal('deposit_amount', 15, 2)->default(0);                                                      
            $table->string('contact_number')->nullable();                                                              
            $table->text('notes')->nullable();                                                                         
            $table->enum('status', ['confirmed', 'completed', 'cancelled'])->default('confirmed');
            $table->foreignId('sale_id')->nullable()->constrained('sales')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};

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
        Schema::create('order_detials', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->nullable();
            $table->string('product_id')->nullable();
            $table->string('box')->nullable();
            $table->string('patti')->nullable();
            $table->string('packet')->nullable();
            $table->string('price')->nullable()->comment('per packet price');
            $table->string('total_qty')->nullable();
            $table->string('total_cost')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_detials');
    }
};

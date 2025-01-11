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
        Schema::create('purchase_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->nullable();
            $table->string('purchase_invoice_id')->nullable();
            $table->string('purchase_price')->nullable();
            $table->string('box')->nullable();
            $table->string('patti')->nullable();
            $table->string('packet')->nullable();
            $table->string('qty')->nullable();
            $table->string('total_amount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_products');
    }
};

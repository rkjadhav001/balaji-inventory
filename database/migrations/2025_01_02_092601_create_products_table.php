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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('thumbnail')->nullable();
            $table->string('name')->nullable();
            $table->string('short_name')->nullable();
            $table->string('hsn')->nullable();
            $table->string('barcode')->nullable();
            $table->string('category_id')->nullable();
            $table->string('selling_price')->nullable()->comment('per piece price');
            $table->string('box')->nullable();
            $table->string('packet')->nullable();
            $table->string('patti')->nullable();
            $table->string('per_patti_piece')->nullable();
            $table->text('unit_types')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

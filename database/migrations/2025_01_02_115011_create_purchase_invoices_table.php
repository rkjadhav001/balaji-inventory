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
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('date')->nullable();
            $table->string('total_purchase_amount')->nullable();
            $table->string('total_gst_amount')->nullable();
            $table->string('total_payable_amount')->nullable();
            $table->string('total_box')->nullable();
            $table->string('total_patti')->nullable();
            $table->string('total_packet')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_invoices');
    }
};

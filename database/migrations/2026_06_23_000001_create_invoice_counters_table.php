<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_counters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('merchant_id');
            $table->string('date_key', 8); // Ymd, per-merchant daily sequence
            $table->unsignedInteger('last_sequence')->default(0);
            $table->timestamps();

            $table->unique(['merchant_id', 'date_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_counters');
    }
};

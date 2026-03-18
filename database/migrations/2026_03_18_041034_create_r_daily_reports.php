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
        Schema::create('r_daily_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');
            $table->integer('total_transactions');
            $table->decimal('total_revenue', 15, 2);
            $table->decimal('total_items_sold', 15, 2);
            $table->boolean('sent_to_whatsapp')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('r_daily_reports');
    }
};

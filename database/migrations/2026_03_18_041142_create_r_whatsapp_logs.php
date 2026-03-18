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
        Schema::create('r_whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('r_daily_reports')->cascadeOnDelete();
            $table->string('phone_number');
            $table->text('message_body')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('r_whatsapp_logs');
    }
};

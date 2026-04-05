<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('amount_paid', 15, 2);
            $table->decimal('change_amount', 15, 2);
            $table->enum('payment_method', ['cash', 'qris']);
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_transactions');
    }
};
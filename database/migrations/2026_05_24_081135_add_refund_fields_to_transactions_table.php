<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE t_transactions MODIFY status ENUM('pending', 'completed', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('t_transactions', function (Blueprint $table) {
            $table->foreignId('refunded_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('refunded_at')->nullable()->after('refunded_by');
            $table->text('refund_reason')->nullable()->after('refunded_at');
        });
    }

    public function down(): void
    {
        Schema::table('t_transactions', function (Blueprint $table) {
            $table->dropForeign(['refunded_by']);
            $table->dropColumn(['refunded_by', 'refunded_at', 'refund_reason']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE t_transactions MODIFY status ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
        }
    }
};

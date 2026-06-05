<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('t_transactions', 'offline_client_id')) {
                $table->string('offline_client_id')->nullable()->unique()->after('invoice_number');
            }

            if (! Schema::hasColumn('t_transactions', 'offline_created_at')) {
                $table->timestamp('offline_created_at')->nullable()->after('offline_client_id');
            }

            if (! Schema::hasColumn('t_transactions', 'synced_at')) {
                $table->timestamp('synced_at')->nullable()->after('offline_created_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('t_transactions', 'offline_client_id')) {
                $table->dropUnique(['offline_client_id']);
                $table->dropColumn('offline_client_id');
            }

            foreach (['offline_created_at', 'synced_at'] as $column) {
                if (Schema::hasColumn('t_transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('t_transactions', 'sync_warning')) {
                $table->text('sync_warning')->nullable()->after('synced_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('t_transactions', 'sync_warning')) {
                $table->dropColumn('sync_warning');
            }
        });
    }
};

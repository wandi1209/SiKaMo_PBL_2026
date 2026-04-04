<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nullable: hanya diisi untuk role 'cashier'
            // Owner tidak perlu karena relasinya ada di m_merchants.owner_id
            $table->foreignId('merchant_id')
                ->nullable()
                ->after('is_active')
                ->constrained('m_merchants')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['merchant_id']);
            $table->dropColumn('merchant_id');
        });
    }
};

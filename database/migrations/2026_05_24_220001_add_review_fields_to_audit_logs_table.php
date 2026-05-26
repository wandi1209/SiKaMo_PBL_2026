<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_logs', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('audit_logs', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }

            if (! Schema::hasColumn('audit_logs', 'review_note')) {
                $table->text('review_note')->nullable()->after('reviewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'reviewed_by')) {
                $table->dropConstrainedForeignId('reviewed_by');
            }

            if (Schema::hasColumn('audit_logs', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }

            if (Schema::hasColumn('audit_logs', 'review_note')) {
                $table->dropColumn('review_note');
            }
        });
    }
};

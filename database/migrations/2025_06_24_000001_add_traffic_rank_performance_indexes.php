<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Add composite indexes to optimize getTrafficRank query performance.
     * These indexes support:
     * - Filtering by record_at range
     * - Grouping by user_id/server_id
     * - Aggregating u + d values
     */
    public function up(): void
    {
        // Add composite index for v2_stat_user to optimize user traffic ranking queries
        Schema::table('v2_stat_user', function (Blueprint $table) {
            // Check if index already exists before adding
            $indexExists = collect(DB::select("SHOW INDEX FROM v2_stat_user WHERE Key_name = 'idx_stat_user_record_user'"))->isNotEmpty();
            if (!$indexExists) {
                $table->index(['record_at', 'user_id'], 'idx_stat_user_record_user');
            }
        });

        // Add composite index for v2_stat_server to optimize node traffic ranking queries
        Schema::table('v2_stat_server', function (Blueprint $table) {
            $indexExists = collect(DB::select("SHOW INDEX FROM v2_stat_server WHERE Key_name = 'idx_stat_server_record_server'"))->isNotEmpty();
            if (!$indexExists) {
                $table->index(['record_at', 'server_id'], 'idx_stat_server_record_server');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_stat_user', function (Blueprint $table) {
            $table->dropIndex('idx_stat_user_record_user');
        });

        Schema::table('v2_stat_server', function (Blueprint $table) {
            $table->dropIndex('idx_stat_server_record_server');
        });
    }
};

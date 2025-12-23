<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        if (!$this->indexExists('v2_stat_user', 'idx_stat_user_record_user')) {
            Schema::table('v2_stat_user', function (Blueprint $table) {
                $table->index(['record_at', 'user_id'], 'idx_stat_user_record_user');
            });
        }

        // Add composite index for v2_stat_server to optimize node traffic ranking queries
        if (!$this->indexExists('v2_stat_server', 'idx_stat_server_record_server')) {
            Schema::table('v2_stat_server', function (Blueprint $table) {
                $table->index(['record_at', 'server_id'], 'idx_stat_server_record_server');
            });
        }
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

    /**
     * Check if an index exists on a table (cross-database compatible)
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $doctrine = $connection->getDoctrineSchemaManager();
        
        try {
            $indexes = $doctrine->listTableIndexes($table);
            return isset($indexes[$indexName]) || isset($indexes[strtolower($indexName)]);
        } catch (\Exception $e) {
            return false;
        }
    }
};

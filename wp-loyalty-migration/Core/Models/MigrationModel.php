<?php

namespace WPLoyalty\Migration\Core\Models;

defined('ABSPATH') or exit;

use WPLoyalty\Migration\App\Model;

class MigrationModel extends Model
{
    protected $table = 'wlmr_migrations';

    public function createTable()
    {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            migration_id varchar(255) NOT NULL,
            source_type varchar(100) NOT NULL,
            status varchar(50) NOT NULL DEFAULT 'pending',
            total_items bigint(20) NOT NULL DEFAULT 0,
            processed_items bigint(20) NOT NULL DEFAULT 0,
            failed_items bigint(20) NOT NULL DEFAULT 0,
            started_at datetime DEFAULT NULL,
            completed_at datetime DEFAULT NULL,
            error_message text DEFAULT NULL,
            settings longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY migration_id (migration_id),
            KEY source_type (source_type),
            KEY status (status),
            KEY started_at (started_at)
        ) $charset_collate;";

        return $sql;
    }

    public function deleteTable()
    {
        return "DROP TABLE IF EXISTS {$this->table}";
    }

    public function getCoreModels()
    {
        return [
            self::class,
            MigrationLogModel::class,
            MigrationSourceModel::class
        ];
    }

    public function createMigration($data)
    {
        $migration_data = [
            'migration_id' => $data['migration_id'],
            'source_type' => $data['source_type'],
            'status' => 'pending',
            'total_items' => $data['total_items'] ?? 0,
            'settings' => json_encode($data['settings'] ?? [])
        ];

        return $this->insert($migration_data);
    }

    public function updateMigrationStatus($migration_id, $status, $additional_data = [])
    {
        $update_data = array_merge(['status' => $status], $additional_data);
        
        if ($status === 'completed') {
            $update_data['completed_at'] = current_time('mysql');
        } elseif ($status === 'in_progress') {
            $update_data['started_at'] = current_time('mysql');
        }

        return $this->updateByMigrationId($migration_id, $update_data);
    }

    public function updateByMigrationId($migration_id, $data)
    {
        global $wpdb;
        
        return $wpdb->update(
            $this->table,
            $data,
            ['migration_id' => $migration_id]
        );
    }

    public function getMigrationByMigrationId($migration_id)
    {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE migration_id = %s",
            $migration_id
        ));
    }

    public function getMigrationsByStatus($status)
    {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE status = %s ORDER BY created_at DESC",
            $status
        ));
    }

    public function getActiveMigrations()
    {
        return $this->getMigrationsByStatus('in_progress');
    }

    public function getCompletedMigrations()
    {
        return $this->getMigrationsByStatus('completed');
    }

    public function getFailedMigrations()
    {
        return $this->getMigrationsByStatus('failed');
    }

    public function getMigrationStats()
    {
        global $wpdb;
        
        $stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_migrations,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_migrations,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_migrations,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as active_migrations,
                SUM(total_items) as total_items_processed,
                SUM(processed_items) as total_items_successful,
                SUM(failed_items) as total_items_failed
            FROM {$this->table}
        ");

        return $stats;
    }

    public function cleanupOldMigrations($days = 30)
    {
        global $wpdb;
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table} WHERE created_at < %s AND status IN ('completed', 'failed')",
            $cutoff_date
        ));
    }
}
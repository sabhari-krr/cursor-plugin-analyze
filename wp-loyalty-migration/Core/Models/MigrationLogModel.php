<?php

namespace WPLoyalty\Migration\Core\Models;

defined('ABSPATH') or exit;

use WPLoyalty\Migration\App\Model;

class MigrationLogModel extends Model
{
    protected $table = 'wlmr_migration_logs';

    public function createTable()
    {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            migration_id varchar(255) NOT NULL,
            level varchar(20) NOT NULL DEFAULT 'info',
            message text NOT NULL,
            context longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY migration_id (migration_id),
            KEY level (level),
            KEY created_at (created_at)
        ) $charset_collate;";

        return $sql;
    }

    public function deleteTable()
    {
        return "DROP TABLE IF EXISTS {$this->table}";
    }

    public function addLog($migration_id, $message, $level = 'info', $context = [])
    {
        $log_data = [
            'migration_id' => $migration_id,
            'level' => $level,
            'message' => $message,
            'context' => json_encode($context)
        ];

        return $this->insert($log_data);
    }

    public function getLogsByMigrationId($migration_id, $limit = 100)
    {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE migration_id = %s ORDER BY created_at DESC LIMIT %d",
            $migration_id,
            $limit
        ));
    }

    public function getLogsByLevel($level, $limit = 100)
    {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE level = %s ORDER BY created_at DESC LIMIT %d",
            $level,
            $limit
        ));
    }

    public function cleanupOldLogs($days = 30)
    {
        global $wpdb;
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table} WHERE created_at < %s",
            $cutoff_date
        ));
    }

    public function getLogStats()
    {
        global $wpdb;
        
        $stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_logs,
                SUM(CASE WHEN level = 'error' THEN 1 ELSE 0 END) as error_logs,
                SUM(CASE WHEN level = 'warning' THEN 1 ELSE 0 END) as warning_logs,
                SUM(CASE WHEN level = 'info' THEN 1 ELSE 0 END) as info_logs,
                SUM(CASE WHEN level = 'debug' THEN 1 ELSE 0 END) as debug_logs
            FROM {$this->table}
        ");

        return $stats;
    }
}
<?php

namespace WPLoyalty\Migration\Core\Models;

defined('ABSPATH') or exit;

use WPLoyalty\Migration\App\Model;

class MigrationSourceModel extends Model
{
    protected $table = 'wlmr_migration_sources';

    public function createTable()
    {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            source_type varchar(100) NOT NULL,
            source_name varchar(255) NOT NULL,
            source_config longtext DEFAULT NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            last_checked datetime DEFAULT NULL,
            last_sync datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY source_type (source_type),
            KEY is_active (is_active)
        ) $charset_collate;";

        return $sql;
    }

    public function deleteTable()
    {
        return "DROP TABLE IF EXISTS {$this->table}";
    }

    public function addSource($source_type, $source_name, $config = [])
    {
        $source_data = [
            'source_type' => $source_type,
            'source_name' => $source_name,
            'source_config' => json_encode($config),
            'is_active' => 1
        ];

        return $this->insert($source_data);
    }

    public function updateSourceConfig($source_type, $config)
    {
        global $wpdb;
        
        return $wpdb->update(
            $this->table,
            [
                'source_config' => json_encode($config),
                'updated_at' => current_time('mysql')
            ],
            ['source_type' => $source_type]
        );
    }

    public function getSourceByType($source_type)
    {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE source_type = %s",
            $source_type
        ));
    }

    public function getActiveSources()
    {
        global $wpdb;
        
        return $wpdb->get_results(
            "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY source_name ASC"
        );
    }

    public function deactivateSource($source_type)
    {
        global $wpdb;
        
        return $wpdb->update(
            $this->table,
            ['is_active' => 0],
            ['source_type' => $source_type]
        );
    }

    public function activateSource($source_type)
    {
        global $wpdb;
        
        return $wpdb->update(
            $this->table,
            ['is_active' => 1],
            ['source_type' => $source_type]
        );
    }

    public function updateLastChecked($source_type)
    {
        global $wpdb;
        
        return $wpdb->update(
            $this->table,
            ['last_checked' => current_time('mysql')],
            ['source_type' => $source_type]
        );
    }

    public function updateLastSync($source_type)
    {
        global $wpdb;
        
        return $wpdb->update(
            $this->table,
            ['last_sync' => current_time('mysql')],
            ['source_type' => $source_type]
        );
    }
}
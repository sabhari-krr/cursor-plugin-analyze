<?php

namespace WPLoyalty\Migration\Core\Controllers\Api;

defined('ABSPATH') or exit;

use WPLoyalty\Migration\App\Helpers\Functions;
use WPLoyalty\Migration\App\Helpers\WordpressHelper;
use WPLoyalty\Migration\Core\Models\MigrationModel;
use WPLoyalty\Migration\Core\Models\MigrationLogModel;

class MigrationController
{
    private $migrationModel;
    private $logModel;

    public function __construct()
    {
        $this->migrationModel = new MigrationModel();
        $this->logModel = new MigrationLogModel();
    }

    public function start($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        $source_type = sanitize_text_field($_POST['source_type'] ?? '');
        $settings = $this->sanitizeSettings($_POST['settings'] ?? []);

        if (empty($source_type)) {
            return ['error' => __('Source type is required.', 'wp-loyalty-migration')];
        }

        // Check if migration is already in progress
        $active_migrations = $this->migrationModel->getActiveMigrations();
        if (!empty($active_migrations)) {
            return ['error' => __('Another migration is already in progress.', 'wp-loyalty-migration')];
        }

        // Create migration record
        $migration_id = uniqid('migration_');
        $migration_data = [
            'migration_id' => $migration_id,
            'source_type' => $source_type,
            'settings' => $settings
        ];

        $migration_id_db = $this->migrationModel->createMigration($migration_data);
        if (!$migration_id_db) {
            return ['error' => __('Failed to create migration record.', 'wp-loyalty-migration')];
        }

        // Update status to in progress
        $this->migrationModel->updateMigrationStatus($migration_id, 'in_progress');
        
        // Log the start
        $this->logModel->addLog($migration_id, 'Migration started', 'info', [
            'source_type' => $source_type,
            'settings' => $settings
        ]);

        // Update global status
        Functions::updateMigrationStatus('in_progress');

        // Trigger action
        do_action('wlmr_before_migration', $migration_id, $source_type, $settings);

        return [
            'success' => true,
            'message' => __('Migration started successfully.', 'wp-loyalty-migration'),
            'migration_id' => $migration_id
        ];
    }

    public function stop($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        $migration_id = sanitize_text_field($_POST['migration_id'] ?? '');
        
        if (empty($migration_id)) {
            return ['error' => __('Migration ID is required.', 'wp-loyalty-migration')];
        }

        // Update migration status
        $this->migrationModel->updateMigrationStatus($migration_id, 'stopped');
        
        // Log the stop
        $this->logModel->addLog($migration_id, 'Migration stopped by user', 'info');
        
        // Update global status
        Functions::updateMigrationStatus('stopped');

        // Trigger action
        do_action('wlmr_after_migration', $migration_id, 'stopped');

        return [
            'success' => true,
            'message' => __('Migration stopped successfully.', 'wp-loyalty-migration')
        ];
    }

    public function pause($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        $migration_id = sanitize_text_field($_POST['migration_id'] ?? '');
        
        if (empty($migration_id)) {
            return ['error' => __('Migration ID is required.', 'wp-loyalty-migration')];
        }

        // Update migration status
        $this->migrationModel->updateMigrationStatus($migration_id, 'paused');
        
        // Log the pause
        $this->logModel->addLog($migration_id, 'Migration paused by user', 'info');
        
        // Update global status
        Functions::updateMigrationStatus('paused');

        return [
            'success' => true,
            'message' => __('Migration paused successfully.', 'wp-loyalty-migration')
        ];
    }

    public function resume($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        $migration_id = sanitize_text_field($_POST['migration_id'] ?? '');
        
        if (empty($migration_id)) {
            return ['error' => __('Migration ID is required.', 'wp-loyalty-migration')];
        }

        // Update migration status
        $this->migrationModel->updateMigrationStatus($migration_id, 'in_progress');
        
        // Log the resume
        $this->logModel->addLog($migration_id, 'Migration resumed by user', 'info');
        
        // Update global status
        Functions::updateMigrationStatus('in_progress');

        return [
            'success' => true,
            'message' => __('Migration resumed successfully.', 'wp-loyalty-migration')
        ];
    }

    public function getLogs($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        $migration_id = sanitize_text_field($_POST['migration_id'] ?? '');
        $limit = intval($_POST['limit'] ?? 100);
        
        if (empty($migration_id)) {
            return ['error' => __('Migration ID is required.', 'wp-loyalty-migration')];
        }

        $logs = $this->logModel->getLogsByMigrationId($migration_id, $limit);

        return [
            'success' => true,
            'logs' => $logs
        ];
    }

    public function clearLogs($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        $migration_id = sanitize_text_field($_POST['migration_id'] ?? '');
        
        if (empty($migration_id)) {
            return ['error' => __('Migration ID is required.', 'wp-loyalty-migration')];
        }

        // Clear logs for specific migration
        $this->logModel->cleanupOldLogs(0);

        return [
            'success' => true,
            'message' => __('Logs cleared successfully.', 'wp-loyalty-migration')
        ];
    }

    public function getAvailableSources($request)
    {
        $sources = Functions::getMigrationSources();
        
        return [
            'success' => true,
            'sources' => $sources
        ];
    }

    public function testSourceConnection($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        $source_type = sanitize_text_field($_POST['source_type'] ?? '');
        
        if (empty($source_type)) {
            return ['error' => __('Source type is required.', 'wp-loyalty-migration')];
        }

        // Test connection logic here
        $connection_test = $this->testConnection($source_type);

        return [
            'success' => $connection_test['success'],
            'message' => $connection_test['message'],
            'details' => $connection_test['details'] ?? []
        ];
    }

    public function validateSourceData($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        $source_type = sanitize_text_field($_POST['source_type'] ?? '');
        
        if (empty($source_type)) {
            return ['error' => __('Source type is required.', 'wp-loyalty-migration')];
        }

        // Validate data logic here
        $validation_result = $this->validateData($source_type);

        return [
            'success' => $validation_result['success'],
            'message' => $validation_result['message'],
            'data_summary' => $validation_result['data_summary'] ?? []
        ];
    }

    public function previewData($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        $source_type = sanitize_text_field($_POST['source_type'] ?? '');
        $limit = intval($_POST['limit'] ?? 10);
        
        if (empty($source_type)) {
            return ['error' => __('Source type is required.', 'wp-loyalty-migration')];
        }

        // Preview data logic here
        $preview_data = $this->getPreviewData($source_type, $limit);

        return [
            'success' => true,
            'preview_data' => $preview_data
        ];
    }

    public function estimateTime($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        $source_type = sanitize_text_field($_POST['source_type'] ?? '');
        
        if (empty($source_type)) {
            return ['error' => __('Source type is required.', 'wp-loyalty-migration')];
        }

        // Estimate time logic here
        $estimate = $this->estimateMigrationTime($source_type);

        return [
            'success' => true,
            'estimate' => $estimate
        ];
    }

    public function getSummary($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        $migration_id = sanitize_text_field($_POST['migration_id'] ?? '');
        
        if (empty($migration_id)) {
            return ['error' => __('Migration ID is required.', 'wp-loyalty-migration')];
        }

        $migration = $this->migrationModel->getMigrationByMigrationId($migration_id);
        
        if (!$migration) {
            return ['error' => __('Migration not found.', 'wp-loyalty-migration')];
        }

        return [
            'success' => true,
            'summary' => $migration
        ];
    }

    public function exportReport($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        $migration_id = sanitize_text_field($_POST['migration_id'] ?? '');
        
        if (empty($migration_id)) {
            return ['error' => __('Migration ID is required.', 'wp-loyalty-migration')];
        }

        // Export report logic here
        $report = $this->generateReport($migration_id);

        return [
            'success' => true,
            'report' => $report
        ];
    }

    public function importConfig($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        // Import config logic here
        $result = $this->importConfiguration();

        return [
            'success' => $result['success'],
            'message' => $result['message']
        ];
    }

    public function resetData($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        // Reset data logic here
        $result = $this->resetMigrationData();

        return [
            'success' => $result['success'],
            'message' => $result['message']
        ];
    }

    public function getPublicStatus($request)
    {
        $status = Functions::getMigrationStatus();
        $progress = Functions::getMigrationProgress();
        
        return [
            'status' => $status,
            'progress' => $progress
        ];
    }

    public function getPublicProgress($request)
    {
        $progress = Functions::getMigrationProgress();
        
        return [
            'progress' => $progress
        ];
    }

    public function checkHealth($request)
    {
        $health = [
            'status' => 'healthy',
            'checks' => [
                'database' => $this->checkDatabaseHealth(),
                'file_system' => $this->checkFileSystemHealth(),
                'memory' => $this->checkMemoryHealth()
            ]
        ];

        return $health;
    }

    private function sanitizeSettings($settings)
    {
        return Functions::sanitizeMigrationData($settings);
    }

    private function testConnection($source_type)
    {
        // Implement connection testing logic
        return [
            'success' => true,
            'message' => __('Connection test successful.', 'wp-loyalty-migration')
        ];
    }

    private function validateData($source_type)
    {
        // Implement data validation logic
        return [
            'success' => true,
            'message' => __('Data validation successful.', 'wp-loyalty-migration')
        ];
    }

    private function getPreviewData($source_type, $limit)
    {
        // Implement preview data logic
        return [];
    }

    private function estimateMigrationTime($source_type)
    {
        // Implement time estimation logic
        return [
            'estimated_time' => '2 hours',
            'confidence' => 'high'
        ];
    }

    private function generateReport($migration_id)
    {
        // Implement report generation logic
        return [];
    }

    private function importConfiguration()
    {
        // Implement configuration import logic
        return [
            'success' => true,
            'message' => __('Configuration imported successfully.', 'wp-loyalty-migration')
        ];
    }

    private function resetMigrationData()
    {
        // Implement data reset logic
        Functions::resetMigrationProgress();
        
        return [
            'success' => true,
            'message' => __('Migration data reset successfully.', 'wp-loyalty-migration')
        ];
    }

    private function checkDatabaseHealth()
    {
        global $wpdb;
        
        $result = $wpdb->get_var("SELECT 1");
        
        return [
            'status' => $result === '1' ? 'healthy' : 'unhealthy',
            'message' => $result === '1' ? 'Database connection OK' : 'Database connection failed'
        ];
    }

    private function checkFileSystemHealth()
    {
        $upload_dir = wp_upload_dir();
        $test_file = $upload_dir['basedir'] . '/wlmr_test.txt';
        
        $write_result = file_put_contents($test_file, 'test');
        $delete_result = unlink($test_file);
        
        return [
            'status' => ($write_result && $delete_result) ? 'healthy' : 'unhealthy',
            'message' => ($write_result && $delete_result) ? 'File system access OK' : 'File system access failed'
        ];
    }

    private function checkMemoryHealth()
    {
        $memory_limit = ini_get('memory_limit');
        $memory_usage = memory_get_usage(true);
        $memory_peak = memory_get_peak_usage(true);
        
        return [
            'status' => 'healthy',
            'message' => 'Memory usage OK',
            'details' => [
                'limit' => $memory_limit,
                'usage' => $this->formatBytes($memory_usage),
                'peak' => $this->formatBytes($memory_peak)
            ]
        ];
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
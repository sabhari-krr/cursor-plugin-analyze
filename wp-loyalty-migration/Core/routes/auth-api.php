<?php

// All routes actions will be performed in Route::handleAuthRequest method.

defined("ABSPATH") or exit;

use WPLoyalty\Migration\Core\Controllers\Api\MigrationController;
use WPLoyalty\Migration\Core\Controllers\Api\SettingsController;
use WPLoyalty\Migration\Core\Controllers\Api\DashboardController;
use WPLoyalty\Migration\Core\Controllers\LocalDataController;

// This actions will be prefixed by Route::constants

return [
    'playground' => ['callable' => [DashboardController::class, 'playground']],
    'get_local_data' => ['callable' => [LocalDataController::class, 'getLocalData']],

    // Dashboard
    'dashboard_get_migration_status' => ['callable' => [DashboardController::class, 'getMigrationStatus']],
    'dashboard_get_migration_progress' => ['callable' => [DashboardController::class, 'getMigrationProgress']],
    'dashboard_get_migration_history' => ['callable' => [DashboardController::class, 'getMigrationHistory']],

    // Migration
    'start_migration' => ['callable' => [MigrationController::class, 'start']],
    'stop_migration' => ['callable' => [MigrationController::class, 'stop']],
    'pause_migration' => ['callable' => [MigrationController::class, 'pause']],
    'resume_migration' => ['callable' => [MigrationController::class, 'resume']],
    'get_migration_logs' => ['callable' => [MigrationController::class, 'getLogs']],
    'clear_migration_logs' => ['callable' => [MigrationController::class, 'clearLogs']],

    // Settings
    'get_migration_settings' => ['callable' => [SettingsController::class, 'getMigrationSettings']],
    'save_migration_settings' => ['callable' => [SettingsController::class, 'saveMigrationSettings']],
    'get_plugin_settings' => ['callable' => [SettingsController::class, 'getPluginSettings']],
    'save_plugin_settings' => ['callable' => [SettingsController::class, 'savePluginSettings']],

    // Migration Sources
    'get_available_sources' => ['callable' => [MigrationController::class, 'getAvailableSources']],
    'test_source_connection' => ['callable' => [MigrationController::class, 'testSourceConnection']],
    'validate_source_data' => ['callable' => [MigrationController::class, 'validateSourceData']],

    // Migration Data
    'preview_migration_data' => ['callable' => [MigrationController::class, 'previewData']],
    'estimate_migration_time' => ['callable' => [MigrationController::class, 'estimateTime']],
    'get_migration_summary' => ['callable' => [MigrationController::class, 'getSummary']],

    // Utilities
    'export_migration_report' => ['callable' => [MigrationController::class, 'exportReport']],
    'import_migration_config' => ['callable' => [MigrationController::class, 'importConfig']],
    'reset_migration_data' => ['callable' => [MigrationController::class, 'resetData']],
];
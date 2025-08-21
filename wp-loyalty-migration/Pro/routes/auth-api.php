<?php

// Pro routes actions will be performed in Route::handleAuthRequest method.

defined("ABSPATH") or exit;

use WPLoyalty\Migration\Pro\Controllers\Api\ProMigrationController;

// Pro-specific API endpoints

return [
    'pro_start_migration' => ['callable' => [ProMigrationController::class, 'startProMigration']],
    'pro_get_advanced_stats' => ['callable' => [ProMigrationController::class, 'getAdvancedStats']],
    'pro_export_advanced_report' => ['callable' => [ProMigrationController::class, 'exportAdvancedReport']],
    'pro_import_advanced_config' => ['callable' => [ProMigrationController::class, 'importAdvancedConfig']],
    'pro_schedule_migration' => ['callable' => [ProMigrationController::class, 'scheduleMigration']],
    'pro_get_scheduled_migrations' => ['callable' => [ProMigrationController::class, 'getScheduledMigrations']],
];
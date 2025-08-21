<?php

// All routes actions will be performed in Route::handleGuestRequest method.

defined("ABSPATH") or exit;

use WPLoyalty\Migration\Core\Controllers\Api\MigrationController;

// Guest accessible API endpoints (no authentication required)

return [
    'get_migration_status' => ['callable' => [MigrationController::class, 'getPublicStatus']],
    'get_migration_progress' => ['callable' => [MigrationController::class, 'getPublicProgress']],
    'check_migration_health' => ['callable' => [MigrationController::class, 'checkHealth']],
];
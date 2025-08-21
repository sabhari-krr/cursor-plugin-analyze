<?php

// All routes actions will be performed in Route::handleAuthRequest method.

defined("ABSPATH") or exit;

use WPLoyalty\Migration\App\Hooks\AdminHooks;
use WPLoyalty\Migration\Core\Models\MigrationModel;

$admin_hooks = [
    'actions' => [
        'admin_init' => ['callable' => [AdminHooks::class, 'init'], 'priority' => 10, 'accepted_args' => 1],
        'admin_head' => ['callable' => [AdminHooks::class, 'head'], 'priority' => 10, 'accepted_args' => 1],
        'admin_menu' => ['callable' => [AdminHooks::class, 'addMenu'], 'priority' => 10, 'accepted_args' => 1],
    ],
    'filters' => [
        'wlmr_migration_get_models' => ['callable' => [MigrationModel::class, 'getCoreModels'], 'priority' => 10, 'accepted_args' => 1],
    ],
];

$store_front_hooks = [
    'actions' => [],
    'filters' => [],
];

return [
    'admin_hooks' => $admin_hooks,
    'store_front_hooks' => $store_front_hooks
];
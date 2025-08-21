<?php

// All routes actions will be performed in Route::handleAuthRequest method.

defined("ABSPATH") or exit;

use WPLoyalty\Migration\App\Hooks\CustomHooks;

$custom_hooks = [
    'actions' => [
        'wlmr_before_migration' => ['callable' => [CustomHooks::class, 'beforeMigration'], 'priority' => 10, 'accepted_args' => 1],
        'wlmr_after_migration' => ['callable' => [CustomHooks::class, 'afterMigration'], 'priority' => 10, 'accepted_args' => 1],
        'wlmr_migration_progress' => ['callable' => [CustomHooks::class, 'migrationProgress'], 'priority' => 10, 'accepted_args' => 1],
    ],
    'filters' => [],
];

$store_front_hooks = [
    'actions' => [],
    'filters' => [],
];

return [
    'custom_hooks' => $custom_hooks,
    'store_front_hooks' => $store_front_hooks
];
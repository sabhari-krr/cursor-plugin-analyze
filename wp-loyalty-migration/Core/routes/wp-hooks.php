<?php

// All routes actions will be performed in Route::handleAuthRequest method.

defined("ABSPATH") or exit;

use WPLoyalty\Migration\App\Hooks\WPHooks;

$wp_hooks = [
    'actions' => [
        'init' => ['callable' => [WPHooks::class, 'init'], 'priority' => 10, 'accepted_args' => 1],
        'wp_loaded' => ['callable' => [WPHooks::class, 'wpLoaded'], 'priority' => 10, 'accepted_args' => 1],
        'admin_init' => ['callable' => [WPHooks::class, 'adminInit'], 'priority' => 10, 'accepted_args' => 1],
    ],
    'filters' => [],
];

$store_front_hooks = [
    'actions' => [],
    'filters' => [],
];

return [
    'wp_hooks' => $wp_hooks,
    'store_front_hooks' => $store_front_hooks
];
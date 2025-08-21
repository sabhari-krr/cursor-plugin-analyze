<?php

// Pro routes actions will be performed in Route::handleAuthRequest method.

defined("ABSPATH") or exit;

use WPLoyalty\Migration\Pro\Controllers\Admin\ProPageController;

$admin_hooks = [
    'actions' => [
        'admin_menu' => ['callable' => [ProPageController::class, 'addProMenu'], 'priority' => 20, 'accepted_args' => 1],
    ],
    'filters' => [],
];

$store_front_hooks = [
    'actions' => [],
    'filters' => [],
];

return [
    'admin_hooks' => $admin_hooks,
    'store_front_hooks' => $store_front_hooks
];
<?php

// All routes actions will be performed in Route::handleAuthRequest method.

defined("ABSPATH") or exit;

use WPLoyalty\Migration\App\Hooks\WooCommerceHooks;

$woocommerce_hooks = [
    'actions' => [
        'woocommerce_init' => ['callable' => [WooCommerceHooks::class, 'init'], 'priority' => 10, 'accepted_args' => 1],
        'woocommerce_order_status_changed' => ['callable' => [WooCommerceHooks::class, 'orderStatusChanged'], 'priority' => 10, 'accepted_args' => 3],
    ],
    'filters' => [],
];

$store_front_hooks = [
    'actions' => [],
    'filters' => [],
];

return [
    'woocommerce_hooks' => $woocommerce_hooks,
    'store_front_hooks' => $store_front_hooks
];
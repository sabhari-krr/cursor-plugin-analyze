<?php

defined("ABSPATH") or exit;

use RelayWp\Affiliate\Pro\Helpers\License;

$store_front_hooks = [
    'actions' => [
        'wp_loaded' => ['callable' => [License::class,  'init'], 'priority' => 10, 'accepted_args' => 1],
    ],
    'filters' => [],
];

$admin_hooks = [
    'actions' => [],
    'filters' => [],
];

return [
    'store_front_hooks' => $store_front_hooks,
    'admin_hooks' => $admin_hooks
];

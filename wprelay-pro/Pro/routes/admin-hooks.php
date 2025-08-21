<?php

//All routes actions will be performed in Route::handleAuthRequest method.

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Hooks\AdminHooks;
use RelayWp\Affiliate\Pro\Models\ProModel;

$admin_hooks = [
    'actions' => [],
    'filters' => [
        'rwpa_affiliate_get_models' => ['callable' => [ProModel::class, 'getProModels'], 'priority' => 11, 'accepted_args' => 1],
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


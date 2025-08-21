<?php

namespace RelayWp\Affiliate\App\Hooks;

use RelayWp\Affiliate\Core\Controllers\StoreFront\AccountController;

defined('ABSPATH') or exit;

class ConditionalHooks extends RegisterHooks
{
    public static function register()
    {
        $endpoint_name = apply_filters('rwpa_affiliate_get_endpoint_name', 'relay-affiliate-marketing');

        add_action("woocommerce_account_{$endpoint_name}_endpoint", [AccountController::class, 'registerAffiliateEndpointContent'], 10, 1);
        // 'woocommerce_account_relay-affiliate-marketing_endpoint' => ['callable' => [AccountController::class, 'registerAffiliateEndpointContent'], 'priority' => 10, 'accepted_args' => 1],
        //
    }
}

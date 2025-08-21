<?php

namespace WPLoyalty\Migration\App\Hooks;

defined('ABSPATH') or exit;

class WooCommerceHooks
{
    public static function register()
    {
        // WooCommerce specific hooks
        add_action('woocommerce_init', [__CLASS__, 'init']);
        add_action('woocommerce_order_status_changed', [__CLASS__, 'orderStatusChanged'], 10, 3);
    }

    public static function init()
    {
        // Initialize WooCommerce specific functionality
    }

    public static function orderStatusChanged($order_id, $old_status, $new_status)
    {
        // Handle order status changes if needed for migration
    }
}
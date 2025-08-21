<?php

namespace WPLoyalty\Migration\App;

defined("ABSPATH") or exit;

use WPLoyalty\Migration\App\Helpers\Functions;
use WPLoyalty\Migration\App\Helpers\PluginHelper;
use WPLoyalty\Migration\App\Helpers\WordpressHelper;
use WPLoyalty\Migration\App\Hooks\AdminHooks;
use WPLoyalty\Migration\App\Hooks\AssetsActions;
use WPLoyalty\Migration\App\Hooks\CustomHooks;
use WPLoyalty\Migration\App\Hooks\WooCommerceHooks;
use WPLoyalty\Migration\App\Hooks\WPHooks;
use WPLoyalty\Migration\App\Hooks\ConditionalHooks;

class Route
{
    // Declare the below constants with unique reference for your plugin
    const AJAX_NAME = 'wlmr_migration';
    const AJAX_NO_PRIV_NAME = 'wlmr_guest_apis';

    public static function register()
    {
        add_action('wp_ajax_nopriv_' . static::AJAX_NO_PRIV_NAME, [__CLASS__, 'handleGuestRequests']);
        add_action('wp_ajax_' . static::AJAX_NAME, [__CLASS__, 'handleAuthRequests']);

        AdminHooks::register();
        AssetsActions::register();
        WooCommerceHooks::register();
        CustomHooks::register();
        WPHooks::register();
        ConditionalHooks::register();
    }

    public static function getRequestObject()
    {
        return new \stdClass(); // Simplified for now, can be enhanced later
    }

    public static function handleAuthRequests()
    {
        $request = static::getRequestObject();

        $method = isset($_POST['method']) ? sanitize_text_field($_POST['method']) : '';

        $isAuthRoute = false;
        $handlers = require(PluginHelper::pluginRoutePath() . '/guest-api.php');

        if (wlmr_app()->get('is_pro_plugin')) {
            $handlers = array_merge($handlers, require(PluginHelper::pluginRoutePath(true) . '/guest-api.php'));
        }

        if (!isset($handlers[$method])) {
            // Loading auth routes
            $handlers = PluginHelper::getAuthRoutes();

            if (wlmr_app()->get('is_pro_plugin')) {
                $handlers = array_merge($handlers, require(PluginHelper::pluginRoutePath(true) . '/auth-api.php'));
            }

            $isAuthRoute = true;
        }

        if ($isAuthRoute) {
            $nonce_key = isset($_POST['_wp_nonce_key']) ? sanitize_text_field($_POST['_wp_nonce_key']) : '';
            $nonce = isset($_POST['_wp_nonce']) ? sanitize_text_field($_POST['_wp_nonce']) : '';

            if ($method != 'get_local_data' && $method != 'playground') {
                static::verifyNonce($nonce_key, $nonce); // to verify nonce
            }
        }

        if (!isset($handlers[$method])) {
            wp_send_json_error(['message' => __('Method not exists', 'wp-loyalty-migration')]);
        }

        $targetAction = $handlers[$method];

        return static::handleRequest($targetAction, $request);
    }

    public static function handleGuestRequests()
    {
        $request = static::getRequestObject();

        $method = isset($_POST['method']) ? sanitize_text_field($_POST['method']) : '';

        // Loading guest routes
        $handlers = require(PluginHelper::pluginRoutePath() . '/guest-api.php');

        if (wlmr_app()->get('is_pro_plugin')) {
            $handlers = array_merge($handlers, require(PluginHelper::pluginRoutePath(true) . '/guest-api.php'));
        }

        if (!isset($handlers[$method])) {
            wp_send_json_error(['message' => 'Method not exists'], 404);
        }

        $targetAction = $handlers[$method];

        return static::handleRequest($targetAction, $request);
    }

    private static function verifyNonce($nonceKey, $nonce)
    {
        if (empty($nonce) || !WordpressHelper::verifyNonce($nonceKey, $nonce)) {
            wp_send_json_error(['message' => __('Security Check Failed', 'wp-loyalty-migration')]);
        }
    }

    public static function handleRequest($targetAction, $request)
    {
        $target = $targetAction['callable'];

        $class = $target[0];
        $targetMethod = $target[1];

        $controller = new $class();

        $response = $controller->{$targetMethod}($request);

        return wp_send_json_success($response);
    }
}
<?php

namespace WPLoyalty\Migration\App\Hooks;

defined('ABSPATH') or exit;

use WPLoyalty\Migration\App\Helpers\PluginHelper;
use WPLoyalty\Migration\Core\Controllers\Admin\PageController;

class AdminHooks extends RegisterHooks
{
    public static function register()
    {
        static::registerCoreHooks('admin-hooks.php');

        if (PluginHelper::isPRO()) {
            static::registerProHooks('admin-hooks.php');
        }
    }

    public static function init() {}

    public static function head() {}

    public static function addMenu()
    {
        $pluginName = WLMR_PLUGIN_NAME;

        add_menu_page(
            $pluginName,
            $pluginName,
            'manage_options',
            WLMR_PLUGIN_SLUG,
            [PageController::class, 'show'],
            'dashicons-migrate',
            56
        );

        // Add submenu pages
        add_submenu_page(
            WLMR_PLUGIN_SLUG,
            __('Migration Dashboard', 'wp-loyalty-migration'),
            __('Dashboard', 'wp-loyalty-migration'),
            'manage_options',
            WLMR_PLUGIN_SLUG,
            [PageController::class, 'show']
        );

        add_submenu_page(
            WLMR_PLUGIN_SLUG,
            __('Migration Settings', 'wp-loyalty-migration'),
            __('Settings', 'wp-loyalty-migration'),
            'manage_options',
            WLMR_PLUGIN_SLUG . '-settings',
            [PageController::class, 'showSettings']
        );

        add_submenu_page(
            WLMR_PLUGIN_SLUG,
            __('Migration History', 'wp-loyalty-migration'),
            __('History', 'wp-loyalty-migration'),
            'manage_options',
            WLMR_PLUGIN_SLUG . '-history',
            [PageController::class, 'showHistory']
        );

        if (PluginHelper::isPRO()) {
            add_submenu_page(
                WLMR_PLUGIN_SLUG,
                __('Pro Features', 'wp-loyalty-migration'),
                __('Pro Features', 'wp-loyalty-migration'),
                'manage_options',
                WLMR_PLUGIN_SLUG . '-pro',
                [PageController::class, 'showProFeatures']
            );
        }
    }
}
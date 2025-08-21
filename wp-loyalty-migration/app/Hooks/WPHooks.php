<?php

namespace WPLoyalty\Migration\App\Hooks;

defined('ABSPATH') or exit;

class WPHooks
{
    public static function register()
    {
        // WordPress specific hooks
        add_action('init', [__CLASS__, 'init']);
        add_action('wp_loaded', [__CLASS__, 'wpLoaded']);
        add_action('admin_init', [__CLASS__, 'adminInit']);
    }

    public static function init()
    {
        // Initialize WordPress specific functionality
        load_plugin_textdomain('wp-loyalty-migration', false, dirname(plugin_basename(WLMR_PLUGIN_FILE)) . '/i18n/languages');
    }

    public static function wpLoaded()
    {
        // Actions to run after WordPress is fully loaded
    }

    public static function adminInit()
    {
        // Admin initialization actions
    }
}
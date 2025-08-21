<?php

namespace WPLoyalty\Migration\App\Hooks;

defined('ABSPATH') or exit;

class AssetsActions
{
    public static function register()
    {
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueueAdminAssets']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueueFrontendAssets']);
    }

    public static function enqueueAdminAssets()
    {
        $screen = get_current_screen();
        
        if (!$screen || strpos($screen->id, WLMR_PLUGIN_SLUG) === false) {
            return;
        }

        // Enqueue admin styles
        wp_enqueue_style(
            'wlmr-admin-style',
            WLMR_PLUGIN_URL . 'resources/css/admin.css',
            [],
            WLMR_VERSION
        );

        // Enqueue admin scripts
        wp_enqueue_script(
            'wlmr-admin-script',
            WLMR_PLUGIN_URL . 'resources/scripts/admin.js',
            ['jquery'],
            WLMR_VERSION,
            true
        );

        // Localize script
        wp_localize_script('wlmr-admin-script', 'wlmr_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wlmr_admin_nonce'),
            'strings' => [
                'confirm_migration' => __('Are you sure you want to start the migration?', 'wp-loyalty-migration'),
                'migration_started' => __('Migration started successfully!', 'wp-loyalty-migration'),
                'migration_error' => __('An error occurred during migration.', 'wp-loyalty-migration'),
                'processing' => __('Processing...', 'wp-loyalty-migration'),
                'completed' => __('Completed!', 'wp-loyalty-migration')
            ]
        ]);
    }

    public static function enqueueFrontendAssets()
    {
        // Enqueue frontend styles if needed
        wp_enqueue_style(
            'wlmr-frontend-style',
            WLMR_PLUGIN_URL . 'resources/css/frontend.css',
            [],
            WLMR_VERSION
        );

        // Enqueue frontend scripts if needed
        wp_enqueue_script(
            'wlmr-frontend-script',
            WLMR_PLUGIN_URL . 'resources/scripts/frontend.js',
            ['jquery'],
            WLMR_VERSION,
            true
        );
    }
}
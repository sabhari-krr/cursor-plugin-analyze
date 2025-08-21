<?php

namespace WPLoyalty\Migration\App\Hooks;

defined('ABSPATH') or exit;

class ConditionalHooks
{
    public static function register()
    {
        // Conditional hooks based on plugin state or settings
        if (is_admin()) {
            add_action('admin_notices', [__CLASS__, 'adminNotices']);
        }

        if (wp_doing_ajax()) {
            add_action('wp_ajax_wlmr_migration_status', [__CLASS__, 'ajaxMigrationStatus']);
            add_action('wp_ajax_wlmr_start_migration', [__CLASS__, 'ajaxStartMigration']);
            add_action('wp_ajax_wlmr_stop_migration', [__CLASS__, 'ajaxStopMigration']);
        }
    }

    public static function adminNotices()
    {
        // Show admin notices if needed
        $migration_status = get_option('wlmr_migration_status', 'not_started');
        
        if ($migration_status === 'in_progress') {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p>' . __('Migration is currently in progress. Please wait for it to complete.', 'wp-loyalty-migration') . '</p>';
            echo '</div>';
        }
    }

    public static function ajaxMigrationStatus()
    {
        // Handle AJAX request for migration status
        check_ajax_referer('wlmr_admin_nonce', 'nonce');
        
        $status = get_option('wlmr_migration_status', 'not_started');
        $progress = \WPLoyalty\Migration\App\Helpers\Functions::getMigrationProgress();
        
        wp_send_json_success([
            'status' => $status,
            'progress' => $progress
        ]);
    }

    public static function ajaxStartMigration()
    {
        // Handle AJAX request to start migration
        check_ajax_referer('wlmr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-loyalty-migration')]);
        }
        
        // Start migration logic here
        update_option('wlmr_migration_status', 'in_progress');
        
        wp_send_json_success(['message' => __('Migration started successfully.', 'wp-loyalty-migration')]);
    }

    public static function ajaxStopMigration()
    {
        // Handle AJAX request to stop migration
        check_ajax_referer('wlmr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-loyalty-migration')]);
        }
        
        // Stop migration logic here
        update_option('wlmr_migration_status', 'stopped');
        
        wp_send_json_success(['message' => __('Migration stopped successfully.', 'wp-loyalty-migration')]);
    }
}
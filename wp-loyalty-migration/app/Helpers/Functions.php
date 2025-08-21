<?php

namespace WPLoyalty\Migration\App\Helpers;

defined('ABSPATH') or exit;

class Functions
{
    /**
     * Get migration status
     */
    public static function getMigrationStatus()
    {
        return get_option('wlmr_migration_status', 'not_started');
    }

    /**
     * Update migration status
     */
    public static function updateMigrationStatus($status)
    {
        return update_option('wlmr_migration_status', $status);
    }

    /**
     * Get batch size
     */
    public static function getBatchSize()
    {
        return get_option('wlmr_batch_size', 100);
    }

    /**
     * Get timeout
     */
    public static function getTimeout()
    {
        return get_option('wlmr_timeout', 300);
    }

    /**
     * Check if logging is enabled
     */
    public static function isLoggingEnabled()
    {
        return get_option('wlmr_enable_logging', true);
    }

    /**
     * Log message
     */
    public static function log($message, $level = 'info')
    {
        if (!self::isLoggingEnabled()) {
            return;
        }

        $log_file = WLMR_PLUGIN_PATH . 'logs/migration.log';
        $log_dir = dirname($log_file);

        if (!is_dir($log_dir)) {
            wp_mkdir_p($log_dir);
        }

        $timestamp = current_time('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;

        error_log($log_entry, 3, $log_file);
    }

    /**
     * Get available migration sources
     */
    public static function getMigrationSources()
    {
        return apply_filters('wlmr_migration_sources', [
            'woocommerce_points_and_rewards' => 'WooCommerce Points and Rewards',
            'yith_woocommerce_points_and_rewards' => 'YITH WooCommerce Points and Rewards',
            'woocommerce_points_rewards' => 'WooCommerce Points Rewards',
            'custom' => 'Custom Migration Source'
        ]);
    }

    /**
     * Check if migration source is available
     */
    public static function isMigrationSourceAvailable($source)
    {
        $sources = self::getMigrationSources();
        return isset($sources[$source]);
    }

    /**
     * Get migration progress
     */
    public static function getMigrationProgress()
    {
        $total_items = get_option('wlmr_total_items', 0);
        $processed_items = get_option('wlmr_processed_items', 0);

        if ($total_items == 0) {
            return 0;
        }

        return round(($processed_items / $total_items) * 100, 2);
    }

    /**
     * Update migration progress
     */
    public static function updateMigrationProgress($processed, $total = null)
    {
        update_option('wlmr_processed_items', $processed);
        
        if ($total !== null) {
            update_option('wlmr_total_items', $total);
        }
    }

    /**
     * Reset migration progress
     */
    public static function resetMigrationProgress()
    {
        delete_option('wlmr_total_items');
        delete_option('wlmr_processed_items');
        delete_option('wlmr_migration_status');
    }

    /**
     * Get migration history
     */
    public static function getMigrationHistory()
    {
        return get_option('wlmr_migration_history', []);
    }

    /**
     * Add migration history entry
     */
    public static function addMigrationHistory($entry)
    {
        $history = self::getMigrationHistory();
        $history[] = array_merge($entry, [
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id()
        ]);

        // Keep only last 50 entries
        if (count($history) > 50) {
            $history = array_slice($history, -50);
        }

        return update_option('wlmr_migration_history', $history);
    }

    /**
     * Sanitize migration data
     */
    public static function sanitizeMigrationData($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitizeMigrationData($value);
            }
        } elseif (is_string($data)) {
            $data = sanitize_text_field($data);
        }

        return $data;
    }

    /**
     * Validate migration data
     */
    public static function validateMigrationData($data, $rules = [])
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            if (!isset($data[$field]) || empty($data[$field])) {
                if (strpos($rule, 'required') !== false) {
                    $errors[$field] = sprintf(__('%s is required', 'wp-loyalty-migration'), $field);
                }
            }
        }

        return $errors;
    }

    /**
     * Get admin page URL
     */
    public static function getAdminPageUrl($params = [])
    {
        $base_url = admin_url('admin.php?page=' . WLMR_PLUGIN_SLUG);
        
        if (!empty($params)) {
            $base_url .= '&' . http_build_query($params);
        }

        return $base_url;
    }

    /**
     * Redirect to admin page
     */
    public static function redirectToAdminPage($params = [], $status = 302)
    {
        $url = self::getAdminPageUrl($params);
        wp_redirect($url, $status);
        exit;
    }

    /**
     * Get nonce action
     */
    public static function getNonceAction($action)
    {
        return 'wlmr_' . $action . '_nonce';
    }

    /**
     * Verify nonce for action
     */
    public static function verifyNonceForAction($action, $nonce)
    {
        $nonce_action = self::getNonceAction($action);
        return wp_verify_nonce($nonce, $nonce_action);
    }

    /**
     * Create nonce for action
     */
    public static function createNonceForAction($action)
    {
        $nonce_action = self::getNonceAction($action);
        return wp_create_nonce($nonce_action);
    }
}
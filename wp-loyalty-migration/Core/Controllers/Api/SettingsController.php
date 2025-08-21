<?php

namespace WPLoyalty\Migration\Core\Controllers\Api;

defined('ABSPATH') or exit;

use WPLoyalty\Migration\App\Helpers\WordpressHelper;

class SettingsController
{
    public function getMigrationSettings($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        $settings = [
            'batch_size' => get_option('wlmr_batch_size', 100),
            'timeout' => get_option('wlmr_timeout', 300),
            'max_retries' => get_option('wlmr_max_retries', 3),
            'enable_logging' => get_option('wlmr_enable_logging', true),
            'log_level' => get_option('wlmr_log_level', 'info'),
            'auto_cleanup_logs' => get_option('wlmr_auto_cleanup_logs', true),
            'log_retention_days' => get_option('wlmr_log_retention_days', 30)
        ];

        return [
            'success' => true,
            'settings' => $settings
        ];
    }

    public function saveMigrationSettings($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        $settings = [
            'batch_size' => intval($_POST['batch_size'] ?? 100),
            'timeout' => intval($_POST['timeout'] ?? 300),
            'max_retries' => intval($_POST['max_retries'] ?? 3),
            'enable_logging' => (bool)($_POST['enable_logging'] ?? true),
            'log_level' => sanitize_text_field($_POST['log_level'] ?? 'info'),
            'auto_cleanup_logs' => (bool)($_POST['auto_cleanup_logs'] ?? true),
            'log_retention_days' => intval($_POST['log_retention_days'] ?? 30)
        ];

        // Validate settings
        $validation = $this->validateMigrationSettings($settings);
        if (!empty($validation)) {
            return [
                'success' => false,
                'errors' => $validation
            ];
        }

        // Save settings
        foreach ($settings as $key => $value) {
            update_option('wlmr_' . $key, $value);
        }

        return [
            'success' => true,
            'message' => __('Migration settings saved successfully.', 'wp-loyalty-migration')
        ];
    }

    public function getPluginSettings($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        $settings = [
            'admin_email' => get_option('wlmr_admin_email', get_option('admin_email')),
            'notification_email' => get_option('wlmr_notification_email', get_option('admin_email')),
            'enable_email_notifications' => get_option('wlmr_enable_email_notifications', true),
            'enable_admin_notifications' => get_option('wlmr_enable_admin_notifications', true),
            'migration_started_notification' => get_option('wlmr_migration_started_notification', true),
            'migration_completed_notification' => get_option('wlmr_migration_completed_notification', true),
            'migration_failed_notification' => get_option('wlmr_migration_failed_notification', true)
        ];

        return [
            'success' => true,
            'settings' => $settings
        ];
    }

    public function savePluginSettings($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        $settings = [
            'admin_email' => sanitize_email($_POST['admin_email'] ?? get_option('admin_email')),
            'notification_email' => sanitize_email($_POST['notification_email'] ?? get_option('admin_email')),
            'enable_email_notifications' => (bool)($_POST['enable_email_notifications'] ?? true),
            'enable_admin_notifications' => (bool)($_POST['enable_admin_notifications'] ?? true),
            'migration_started_notification' => (bool)($_POST['migration_started_notification'] ?? true),
            'migration_completed_notification' => (bool)($_POST['migration_completed_notification'] ?? true),
            'migration_failed_notification' => (bool)($_POST['migration_failed_notification'] ?? true)
        ];

        // Validate settings
        $validation = $this->validatePluginSettings($settings);
        if (!empty($validation)) {
            return [
                'success' => false,
                'errors' => $validation
            ];
        }

        // Save settings
        foreach ($settings as $key => $value) {
            update_option('wlmr_' . $key, $value);
        }

        return [
            'success' => true,
            'message' => __('Plugin settings saved successfully.', 'wp-loyalty-migration')
        ];
    }

    private function validateMigrationSettings($settings)
    {
        $errors = [];

        if ($settings['batch_size'] < 1 || $settings['batch_size'] > 10000) {
            $errors['batch_size'] = __('Batch size must be between 1 and 10000.', 'wp-loyalty-migration');
        }

        if ($settings['timeout'] < 30 || $settings['timeout'] > 3600) {
            $errors['timeout'] = __('Timeout must be between 30 and 3600 seconds.', 'wp-loyalty-migration');
        }

        if ($settings['max_retries'] < 0 || $settings['max_retries'] > 10) {
            $errors['max_retries'] = __('Max retries must be between 0 and 10.', 'wp-loyalty-migration');
        }

        if ($settings['log_retention_days'] < 1 || $settings['log_retention_days'] > 365) {
            $errors['log_retention_days'] = __('Log retention days must be between 1 and 365.', 'wp-loyalty-migration');
        }

        $valid_log_levels = ['debug', 'info', 'warning', 'error'];
        if (!in_array($settings['log_level'], $valid_log_levels)) {
            $errors['log_level'] = __('Invalid log level.', 'wp-loyalty-migration');
        }

        return $errors;
    }

    private function validatePluginSettings($settings)
    {
        $errors = [];

        if (!is_email($settings['admin_email'])) {
            $errors['admin_email'] = __('Invalid admin email address.', 'wp-loyalty-migration');
        }

        if (!is_email($settings['notification_email'])) {
            $errors['notification_email'] = __('Invalid notification email address.', 'wp-loyalty-migration');
        }

        return $errors;
    }
}
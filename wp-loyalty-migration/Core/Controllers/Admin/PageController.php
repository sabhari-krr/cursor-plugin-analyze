<?php

namespace WPLoyalty\Migration\Core\Controllers\Admin;

defined('ABSPATH') or exit;

use WPLoyalty\Migration\App\Helpers\Functions;
use WPLoyalty\Migration\App\Helpers\WordpressHelper;

class PageController
{
    public function show()
    {
        if (!WordpressHelper::isAdmin()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-loyalty-migration'));
        }

        $this->renderDashboard();
    }

    public function showSettings()
    {
        if (!WordpressHelper::isAdmin()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-loyalty-migration'));
        }

        $this->renderSettings();
    }

    public function showHistory()
    {
        if (!WordpressHelper::isAdmin()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-loyalty-migration'));
        }

        $this->renderHistory();
    }

    public function showProFeatures()
    {
        if (!WordpressHelper::isAdmin()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-loyalty-migration'));
        }

        $this->renderProFeatures();
    }

    private function renderDashboard()
    {
        $migration_status = Functions::getMigrationStatus();
        $migration_progress = Functions::getMigrationProgress();
        $migration_history = Functions::getMigrationHistory();

        $data = [
            'migration_status' => $migration_status,
            'migration_progress' => $migration_progress,
            'migration_history' => $migration_history,
            'available_sources' => Functions::getMigrationSources(),
            'current_page' => 'dashboard'
        ];

        $this->renderTemplate('dashboard', $data);
    }

    private function renderSettings()
    {
        $settings = $this->getSettings();
        
        $data = [
            'settings' => $settings,
            'current_page' => 'settings'
        ];

        $this->renderTemplate('settings', $data);
    }

    private function renderHistory()
    {
        $migration_history = Functions::getMigrationHistory();
        
        $data = [
            'migration_history' => $migration_history,
            'current_page' => 'history'
        ];

        $this->renderTemplate('history', $data);
    }

    private function renderProFeatures()
    {
        $data = [
            'current_page' => 'pro'
        ];

        $this->renderTemplate('pro-features', $data);
    }

    private function getSettings()
    {
        return [
            'migration_settings' => [
                'batch_size' => get_option('wlmr_batch_size', 100),
                'timeout' => get_option('wlmr_timeout', 300),
                'enable_logging' => get_option('wlmr_enable_logging', true),
                'log_retention_days' => get_option('wlmr_log_retention_days', 30)
            ],
            'notification_settings' => [
                'admin_email' => get_option('wlmr_admin_email', get_option('admin_email')),
                'enable_email_notifications' => get_option('wlmr_enable_email_notifications', true)
            ]
        ];
    }

    private function renderTemplate($template, $data = [])
    {
        // Check for theme override first
        $theme_template = get_template_directory() . '/wp-loyalty-migration/' . $template . '.php';
        
        if (file_exists($theme_template)) {
            $template_path = $theme_template;
        } else {
            $template_path = WLMR_PLUGIN_PATH . 'resources/templates/' . $template . '.php';
        }

        if (file_exists($template_path)) {
            extract($data);
            include $template_path;
        } else {
            echo '<div class="wrap">';
            echo '<h1>' . __('Template not found', 'wp-loyalty-migration') . '</h1>';
            echo '<p>' . sprintf(__('Template file %s not found.', 'wp-loyalty-migration'), $template) . '</p>';
            echo '</div>';
        }
    }
}
<?php

namespace WPLoyalty\Migration\Core\Controllers;

defined('ABSPATH') or exit;

use WPLoyalty\Migration\App\Helpers\Functions;
use WPLoyalty\Migration\App\Helpers\WordpressHelper;

class LocalDataController
{
    public function getLocalData()
    {
        $data = [
            'plugin_info' => [
                'name' => WLMR_PLUGIN_NAME,
                'version' => WLMR_VERSION,
                'slug' => WLMR_PLUGIN_SLUG,
                'is_pro' => wlmr_app()->get('is_pro_plugin') === true
            ],
            'system_info' => [
                'php_version' => PHP_VERSION,
                'wp_version' => get_bloginfo('version'),
                'wc_version' => defined('WC_VERSION') ? WC_VERSION : 'Not installed',
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size')
            ],
            'migration_status' => [
                'current_status' => Functions::getMigrationStatus(),
                'progress' => Functions::getMigrationProgress(),
                'total_items' => get_option('wlmr_total_items', 0),
                'processed_items' => get_option('wlmr_processed_items', 0)
            ],
            'available_sources' => Functions::getMigrationSources(),
            'settings' => [
                'batch_size' => Functions::getBatchSize(),
                'timeout' => Functions::getTimeout(),
                'logging_enabled' => Functions::isLoggingEnabled()
            ],
            'user_info' => [
                'user_id' => WordpressHelper::getCurrentUserId(),
                'is_admin' => WordpressHelper::isAdmin(),
                'is_woocommerce_admin' => WordpressHelper::isWooCommerceAdmin(),
                'user_roles' => $this->getUserRoles()
            ],
            'server_info' => [
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'server_protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown',
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
                'server_port' => $_SERVER['SERVER_PORT'] ?? 'Unknown',
                'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'
            ],
            'database_info' => [
                'db_version' => get_option('db_version'),
                'db_charset' => DB_CHARSET,
                'db_collate' => DB_COLLATE,
                'table_prefix' => $GLOBALS['wpdb']->prefix
            ],
            'active_plugins' => $this->getActivePlugins(),
            'theme_info' => [
                'name' => wp_get_theme()->get('Name'),
                'version' => wp_get_theme()->get('Version'),
                'author' => wp_get_theme()->get('Author'),
                'template' => get_template()
            ]
        ];

        return $data;
    }

    private function getUserRoles()
    {
        $user = WordpressHelper::getCurrentUser();
        if (!$user) {
            return [];
        }

        return $user->roles ?? [];
    }

    private function getActivePlugins()
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $active_plugins = get_option('active_plugins', []);
        $plugins = [];

        foreach ($active_plugins as $plugin) {
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
            $plugins[] = [
                'plugin' => $plugin,
                'name' => $plugin_data['Name'] ?? 'Unknown',
                'version' => $plugin_data['Version'] ?? 'Unknown',
                'author' => $plugin_data['Author'] ?? 'Unknown'
            ];
        }

        return $plugins;
    }
}
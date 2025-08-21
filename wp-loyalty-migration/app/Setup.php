<?php

namespace WPLoyalty\Migration\App;

defined("ABSPATH") or exit;

class Setup
{
    /**
     * Init setup
     */
    public static function init()
    {
        register_activation_hook(WLMR_PLUGIN_FILE, [__CLASS__, 'activate']);
        register_deactivation_hook(WLMR_PLUGIN_FILE, [__CLASS__, 'deactivate']);
        register_uninstall_hook(WLMR_PLUGIN_FILE, [__CLASS__, 'uninstall']);

        add_action('plugins_loaded', [__CLASS__, 'maybeRunMigration']);
    }

    /**
     * Run plugin activation scripts
     */
    public static function activate() 
    {
        // Create database tables
        static::createTables();
        
        // Set default options
        static::setDefaultOptions();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Run plugin activation scripts
     */
    public static function deactivate()
    {
        // Clear scheduled hooks if any
        wp_clear_scheduled_hook('wlmr_migration_cleanup');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Run plugin activation scripts
     */
    public static function uninstall()
    {
        // Check if user has permission to uninstall
        if (!current_user_can('activate_plugins')) {
            return;
        }

        // Get models and delete tables
        $models = static::getModels();

        global $wpdb;
        foreach ($models as $model) {
            $object = (new $model);

            if (method_exists($object, 'deleteTable')) {
                $query = $object->deleteTable();
                $wpdb->query("SET foreign_key_checks = 0;");
                $wpdb->query($query);
                $wpdb->query("SET foreign_key_checks = 1;");
            }
        }

        // Delete options
        delete_option('wlmr_current_version');
        delete_option('wlmr_plugin_settings');
        delete_option('wlmr_migration_status');
    }

    /**
     * Maybe run database migration
     */
    public static function maybeRunMigration()
    {
        $current_version = get_option('wlmr_current_version', '0.0.0');

        if (version_compare(WLMR_VERSION, $current_version, '>')) {
            if (!is_admin()) {
                return;
            }

            static::runMigration();
            update_option('wlmr_current_version', WLMR_VERSION);
        }
    }

    /**
     * Run database migration
     */
    private static function runMigration()
    {
        $models = static::getModels();

        foreach ($models as $model) {
            $object = (new $model);

            if (method_exists($object, 'createTable')) {
                $query = $object->createTable();
                if (method_exists($object, 'executeDatabaseQuery')) {
                    $object->executeDatabaseQuery($query);
                } else {
                    global $wpdb;
                    $wpdb->query($query);
                }
            }
        }
    }

    /**
     * Create database tables
     */
    private static function createTables()
    {
        $models = static::getModels();

        foreach ($models as $model) {
            $object = (new $model);

            if (method_exists($object, 'createTable')) {
                $query = $object->createTable();
                if (method_exists($object, 'executeDatabaseQuery')) {
                    $object->executeDatabaseQuery($query);
                } else {
                    global $wpdb;
                    $wpdb->query($query);
                }
            }
        }
    }

    /**
     * Set default options
     */
    private static function setDefaultOptions()
    {
        $default_options = [
            'wlmr_migration_status' => 'not_started',
            'wlmr_batch_size' => 100,
            'wlmr_timeout' => 300,
            'wlmr_enable_logging' => true
        ];

        foreach ($default_options as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }

    /**
     * @return string[]
     */
    public static function getModels(): array
    {
        return apply_filters('wlmr_migration_get_models', [
            // Add your model classes here
        ]);
    }
}
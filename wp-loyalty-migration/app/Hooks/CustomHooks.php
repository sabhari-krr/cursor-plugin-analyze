<?php

namespace WPLoyalty\Migration\App\Hooks;

defined('ABSPATH') or exit;

class CustomHooks
{
    public static function register()
    {
        // Custom hooks for the plugin
        add_action('wlmr_before_migration', [__CLASS__, 'beforeMigration']);
        add_action('wlmr_after_migration', [__CLASS__, 'afterMigration']);
        add_action('wlmr_migration_progress', [__CLASS__, 'migrationProgress']);
    }

    public static function beforeMigration()
    {
        // Actions to run before migration starts
        do_action('wlmr_log', 'Migration started', 'info');
    }

    public static function afterMigration()
    {
        // Actions to run after migration completes
        do_action('wlmr_log', 'Migration completed', 'info');
    }

    public static function migrationProgress($progress)
    {
        // Handle migration progress updates
        do_action('wlmr_log', "Migration progress: {$progress}%", 'info');
    }
}
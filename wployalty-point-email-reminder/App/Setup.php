<?php

namespace WLPR\App;

use WLPR\App\Helpers\CompatibleCheck;
use WLPR\App\Models\ReminderMailLog;
use WLPR\App\Models\ScheduledJobs;

class Setup{

    /**
     * Register activation, deactivation and uninstall hooks
     * 
     * @return void
     */
    public static function init(){
        register_activation_hook(WLPR_PLUGIN_FILE, [__CLASS__, 'onActivation']);
        register_deactivation_hook(WLPR_PLUGIN_FILE, [__CLASS__, 'onDeactivation']);
        register_uninstall_hook(WLPR_PLUGIN_FILE, [__CLASS__, 'onUninstall']);

        add_filter('plugin_row_meta', [__CLASS__, 'getPluginRowMeta'], 10, 2);
        add_action('plugins_loaded', [__CLASS__, 'maybeRunMigration']);
        add_action('upgrader_process_complete', [__CLASS__, 'maybeRunMigration']);
    }

    /**
     * Check dependencies and run migration
     * 
     * @return void
     */
    public static function onActivation(){
        CompatibleCheck::checkDependencies(true);
        self::maybeRunMigration();
    }

    /**
     * Deactivation hook
     * 
     * @return void
     */
    public static function onDeactivation(){
    }

    /**
     * Uninstall hook
     * 
     * @return void
     */
    public static function onUninstall(){
        // Silence is golden
    }

    /**
     * Check if migration is needed and run it
     * 
     * @return void
     */
    public static function maybeRunMigration(){
        $db_version = get_option('wlrp_version', '0.0.1');
        if(version_compare($db_version, WLPR_PLUGIN_VERSION, '<')){
            self::runMigration();
            update_option('wlrp_version', WLPR_PLUGIN_VERSION);
        }
    }

    /**
     * Run migration
     * 
     * @return void
     */
    public static function runMigration(){
        $models = [
            new ScheduledJobs(),
            new ReminderMailLog(),
        ];
        foreach($models as $model){
            if(is_a($model, '\Wlr\App\Models\Base')){
                $model->create();
            }
        }
    }

    /**
     * Retrieves the plugin row meta to be displayed on the Woocommerce appointments plugin page.
     *
     * @param array $links The existing plugin row meta links.
     * @param string $file The path to the plugin file.
     *
     * @return array
     */
    public static function getPluginRowMeta($links, $file)
    {
        if ($file != plugin_basename(WLPR_PLUGIN_FILE)) {
            return $links;
        }
        $row_meta = [
            'support' => '<a href="' . esc_url('https://wployalty.net/support/') . '" aria-label="' . esc_attr__('Support', 'wployalty-point-email-reminder') . '">' . esc_html__('Support', 'wployalty-point-email-reminder') . '</a>',
        ];

        return array_merge($links, $row_meta);
    }
}
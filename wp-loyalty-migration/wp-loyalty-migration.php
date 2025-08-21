<?php

/**
 * Plugin Name:          WPLoyalty Migration
 * Description:          Migration tool for WPLoyalty plugin data
 * Version:              1.0.0
 * Requires at least:    5.9
 * Requires PHP:         7.3
 * Author:               WPLoyalty
 * Author URI:           https://wployalty.net
 * Text Domain:          wp-loyalty-migration
 * Domain Path:          /i18n/languages
 * License:              GPL v3 or later
 * License URI:           https://www.gnu.org/licenses/gpl-3.0.html
 * Requires Plugins:     woocommerce
 * WC requires at least: 7.0
 */

defined('ABSPATH') or exit;

defined('WLMR_PLUGIN_PATH') or define('WLMR_PLUGIN_PATH', plugin_dir_path(__FILE__));
defined('WLMR_PLUGIN_URL') or define('WLMR_PLUGIN_URL', plugin_dir_url(__FILE__));
defined('WLMR_PLUGIN_FILE') or define('WLMR_PLUGIN_FILE', __FILE__);
defined('WLMR_PLUGIN_NAME') or define('WLMR_PLUGIN_NAME', 'WPLoyalty Migration');
defined('WLMR_PLUGIN_SLUG') or define('WLMR_PLUGIN_SLUG', "wp-loyalty-migration");
defined('WLMR_VERSION') or define('WLMR_VERSION', "1.0.0");
defined('WLMR_PREFIX') or define('WLMR_PREFIX', "wlmr_");
defined('WLMR_TEMPLATE_OVERRIDE_DIR_PATH') or define('WLMR_TEMPLATE_OVERRIDE_DIR_PATH', "wp-loyalty-migration/");

/**
 * Required PHP Version
 */
if (!defined('WLMR_REQUIRED_PHP_VERSION')) {
    define('WLMR_REQUIRED_PHP_VERSION', 7.3);
}

$php_version = phpversion();

if (version_compare($php_version, WLMR_REQUIRED_PHP_VERSION, '<=')) {
    $message = WLMR_PLUGIN_NAME . ": Minimum PHP Version Required Is " . WLMR_REQUIRED_PHP_VERSION;
    $status = 'warning';

    add_action('admin_notices', function () use ($message, $status) {
?>
        <div class="notice notice-<?php echo esc_attr($status); ?>">
            <p><?php echo wp_kses_post($message); ?></p>
        </div>
        <?php
    }, 1);
    return;
}

/**
 * Required Woocommerce Version
 */
if (!defined('WLMR_WC_REQUIRED_VERSION')) {
    define('WLMR_WC_REQUIRED_VERSION', '7.0.0');
}

// To load PSR4 autoloader
if (file_exists(WLMR_PLUGIN_PATH . '/vendor/autoload.php')) {
    require WLMR_PLUGIN_PATH . '/vendor/autoload.php';
} else {
    return;
}

if (!function_exists('wlmr_check_is_woo_commerce_installed')) {
    function wlmr_check_is_woo_commerce_installed()
    {
        $plugin_path = trailingslashit(WP_PLUGIN_DIR) . 'woocommerce/woocommerce.php';
        if (
            in_array($plugin_path, wp_get_active_and_valid_plugins())
            || (is_multisite() && in_array($plugin_path, wp_get_active_network_plugins()))
        ) {
            return true;
        } else {
            $message = WLMR_PLUGIN_NAME . ": WooCommerce Not Activated and it should be minimum version of => " . WLMR_WC_REQUIRED_VERSION;
            $status = 'warning';

            add_action('admin_notices', function () use ($message, $status) {
        ?>
                <div class="notice notice-<?php echo esc_attr($status); ?>">
                    <p><?php echo wp_kses_post($message); ?></p>
                </div>
            <?php
            }, 1);
            return false;
        }
    }
}

if (function_exists('wlmr_check_is_woo_commerce_installed')) {
    if (wlmr_check_is_woo_commerce_installed()) {
        if (defined('WC_VERSION') && version_compare(WC_VERSION, WLMR_WC_REQUIRED_VERSION, '<=')) {
            $message = WLMR_PLUGIN_NAME . ": WooCommerce minimum version Should be => " . WLMR_WC_REQUIRED_VERSION;
            $status = 'warning';

            add_action('admin_notices', function () use ($message, $status) {
            ?>
                <div class="notice notice-<?php echo esc_attr($status); ?>">
                    <p><?php echo wp_kses_post($message); ?></p>
                </div>
        <?php
            }, 1);
            return false;
        }
    } else {
        return;
    }

    /**
     * To set plugin is compatible for WC Custom Order Table (HPOS) feature.
     */
    add_action('before_woocommerce_init', function () {
        if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        }
    });
} else {
    $message = "Unable to register following function wlmr_check_is_woo_commerce_installed";
    $status = 'warning';
    add_action('admin_notices', function () use ($message, $status) {
        ?>
        <div class="notice notice-<?php echo esc_attr($status); ?>">
            <p><?php echo wp_kses_post($message); ?></p>
        </div>
    <?php
    }, 1);
    return;
}

if (!function_exists('wlmr_app')) {
    function wlmr_app()
    {
        return \WPLoyalty\Migration\App\App::make();
    }
}

// To bootstrap the plugin
if (class_exists('WPLoyalty\Migration\App\App')) {
    $app = wlmr_app();
    
    // Check Whether it is PRO USER (for future extensibility)
    $proDirectoryPath = __DIR__ . '/Pro';
    if (is_dir($proDirectoryPath)) {
        $isPro = $app->set('is_pro_plugin', true);
    }

    $app->bootstrap(); // to load the plugin
} else {
    return;
}

if (!function_exists('addWLMRMigrationExtraPluginData')) {
    function addWLMRMigrationExtraPluginData($header)
    {
        $header[] = 'WPLoyaltyMigration';
        $header[] = 'WPLoyaltyMigration Icon';
        $header[] = 'WPLoyaltyMigration Document Link';
        $header[] = 'WPLoyaltyMigration Page Link';
        return $header;
    }
}
add_filter('extra_plugin_headers', 'addWLMRMigrationExtraPluginData');
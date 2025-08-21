<?php

/**
 * Plugin Name:          Relay Affiliate 
 * Description:          Affiliate Marketing for Woo Commerce
 * Version:              1.0.5.2
 * Requires at least:    5.9
 * Requires PHP:         7.3
 * Author:               Relay Affiliate
 * Author URI:           https://www.wprelay.com
 * Text Domain:          relay-affiliate-marketing 
 * Domain Path:          /i18n/languages
 * License:              GPL v3 or later
 * License URI:          https://www.gnu.org/licenses/gpl-3.0.html
 * Requires Plugins: woocommerce
 * WC requires at least: 7.0
 */


defined('ABSPATH') or exit;

defined('RWPA_PLUGIN_PATH') or define('RWPA_PLUGIN_PATH', plugin_dir_path(__FILE__));
defined('RWPA_PLUGIN_URL') or define('RWPA_PLUGIN_URL', plugin_dir_url(__FILE__));
defined('RWPA_PLUGIN_FILE') or define('RWPA_PLUGIN_FILE', __FILE__);
defined('RWPA_PLUGIN_NAME') or define('RWPA_PLUGIN_NAME', 'Relay');
defined('RWPA_PLUGIN_SLUG') or define('RWPA_PLUGIN_SLUG', "relay-affiliate-marketing");
defined('RWPA_VERSION') or define('RWPA_VERSION', "1.0.5.2");
defined('RWPA_PREFIX') or define('RWPA_PREFIX', "prefix_");
defined('RWPA_TEMPLATE_OVERRIDE_DIR_PATH') or define('RWPA_TEMPLATE_OVERRIDE_DIR_PATH', "wprelay/");

/**
 * Required PHP Version
 */
if (!defined('RWPA_REQUIRED_PHP_VERSION')) {
    define('RWPA_REQUIRED_PHP_VERSION', 7.2);
}

$php_version = phpversion();

if (version_compare($php_version, RWPA_REQUIRED_PHP_VERSION, '<=')) {
    $message = RWPA_PLUGIN_NAME . ": Minimum PHP Version Required Is " . RWPA_REQUIRED_PHP_VERSION;
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
 * e */
if (!defined('RWPA_WC_REQUIRED_VERSION')) {
    define('RWPA_WC_REQUIRED_VERSION', '7.0.0');
}


// To load PSR4 autoloader
if (file_exists(RWPA_PLUGIN_PATH . '/vendor/autoload.php')) {
    require RWPA_PLUGIN_PATH . '/vendor/autoload.php';
} else {
    //    wp_die('{plugin_name} is unable to find the autoload file.');
    return;
}

if (!function_exists('rwpa_check_is_woo_commerce_installed')) {
    function rwpa_check_is_woo_commerce_installed()
    {
        $plugin_path = trailingslashit(WP_PLUGIN_DIR) . 'woocommerce/woocommerce.php';
        if (

            in_array($plugin_path, wp_get_active_and_valid_plugins())

            || (is_multisite() && in_array($plugin_path, wp_get_active_network_plugins()))

        ) {
            return true;
            //woocommerce installed
        } else {
            $message = RWPA_PLUGIN_NAME . ": Woo Commerce Not Activated and it should be minimum version of => " . RWPA_WC_REQUIRED_VERSION;
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

if (function_exists('rwpa_check_is_woo_commerce_installed')) {
    if (rwpa_check_is_woo_commerce_installed()) {
        if (defined('WC_VERSION') && version_compare(WC_VERSION, RWPA_WC_REQUIRED_VERSION, '<=')) {
            $message = RWPA_PLUGIN_NAME . ": Woo Commerce minimum version Should be => " . RWPA_WC_REQUIRED_VERSION;
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
    $message = "Unable to register following function rwpa_check_is_woo_commerce_installed";
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

//Loading woo-commerce action schedular
require_once(plugin_dir_path(__FILE__) . '../woocommerce/packages/action-scheduler/action-scheduler.php');

if (!function_exists('rwpa_app')) {
    function rwpa_app()
    {
        return \RelayWp\Affiliate\App\App::make();
    }
}

//here __FILE__ Will Return the Included File Path so it the base of the starting point.
// To bootstrap the plugin
if (class_exists('RelayWp\Affiliate\App\App')) {
    $app = rwpa_app();
    //If the Directory Exists it means it's a pro pack;
    //Check Whether it is PRO USER
    $proDirectoryPath = __DIR__ . '/Pro';

    if (is_dir($proDirectoryPath)) {
        $isPro = $app->set('is_pro_plugin', true);
    }

    $app->bootstrap(); // to load the plugin
} else {
    //    wp_die('Plugin is unable to find the App class.');
    return;
}


if (!function_exists('addRWPARelayExtraPluginData')) {
    function addRWPARelayExtraPluginData($header)
    {
        $header[] = 'RelayWP';
        $header[] = 'RelayWP Icon';
        $header[] = 'RelayWP Document Link';
        $header[] = 'RelayWP Page Link';
        return $header;
    }
}
add_filter('extra_plugin_headers', 'addRWPARelayExtraPluginData');

add_action('admin_head', function () {
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page = !empty($_GET['page']) ? $_GET['page'] : '';
    if (in_array($page, array(RWPA_PLUGIN_SLUG))) {
    ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                self = window;
            });
        </script>
<?php
    }
}, 11);

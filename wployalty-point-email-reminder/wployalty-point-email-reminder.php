<?php
/**
 * Plugin Name: WPLoyalty: Point Email Reminder
 * Plugin URI: https://wployalty.net/
 * Description: This is the addon to send email reminder to admin and users regarding loyalty data.
 * Version: 0.0.1
 * Author: WPLoyalty
 * Slug: wployalty-point-email-reminder
 * Text Domain: wployalty-point-email-reminder
 * Domain Path: /i18n/languages/
 * Requires at least: 4.9.0
 * Requires PHP: 7.0
 * WC requires at least: 6.5
 * WC tested up to: 10.2
 * Contributors: Sabhari
 * Author URI: https://wployalty.net/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins: woocommerce
 */

defined( 'ABSPATH' ) or die();

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use WLPR\App\Helpers\CompatibleCheck;
use WLPR\App\Router;
use WLPR\App\Setup;

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( FeaturesUtil::class ) ) {
		FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__);	
	}
} );


/**
 * Function to check parent plugin wployalty activate or not
 */
if ( ! function_exists( 'isWployaltyActiveOrNotInPointRemainder' ) ) {
	function isWployaltyActiveOrNotInPointRemainder() {
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );
		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		return array_key_exists( 'wp-loyalty-rules/wp-loyalty-rules.php', $active_plugins ) || in_array( 'wp-loyalty-rules/wp-loyalty-rules.php', $active_plugins, false ) || in_array( 'wp-loyalty-rules-lite/wp-loyalty-rules-lite.php', $active_plugins, false ) || in_array( 'wployalty/wp-loyalty-rules-lite.php', $active_plugins, false );
	}
}

// Check if woocommerce is active
if (!function_exists('isWLPRWoocommerceActive')) {
	function isWLPRWoocommerceActive() {
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', [] ) );
		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', [] ) );
		}

		return in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) || in_array( 'woocommerce/woocommerce.php', $active_plugins);
	}
}

//Check if  wployalty is active
if( !function_exists('isWLPRWployaltyActive')) {
	function isWLPRWployaltyActive() {
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', [] ) );
		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', [] ) );
		}

		return in_array('wp-loyalty-rules/wp-loyalty-rules-lite.php', $active_plugins) || array_key_exists('wp-loyalty-rules/wp-loyalty-rules-lite.php', $active_plugins)
			|| in_array('wp-loyalty-rules/wp-loyalty-rules.php', $active_plugins) || array_key_exists('wp-loyalty-rules/wp-loyalty-rules.php', $active_plugins)
			|| in_array('wployalty/wp-loyalty-rules-lite.php', $active_plugins) || array_key_exists('wployalty/wp-loyalty-rules-lite.php', $active_plugins);
	}
}

if ( !isWLPRWoocommerceActive() || !isWLPRWployaltyActive() ) {
	return;
}

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
	return;
}
require_once __DIR__ . '/vendor/autoload.php';

//Define the plugin version
defined('WLPR_PLUGIN_VERSION') or define('WLPR_PLUGIN_VERSION', '1.0.0');
defined('WLPR_PLUGIN_SLUG') or define('WLPR_PLUGIN_SLUG', 'wployalty-point-email-reminder');
defined('WLPR_PLUGIN_PATH') or define('WLPR_PLUGIN_PATH', __DIR__ . '/');
defined('WLPR_PLUGIN_NAME') or define('WLPR_PLUGIN_NAME', 'WPLoyalty: Point Email Reminder');
defined('WLPR_PLUGIN_FILE') or define('WLPR_PLUGIN_FILE', __FILE__);
defined('WLPR_PLUGIN_AUTHOR') or define('WLPR_PLUGIN_AUTHOR', 'WPLoyalty');
defined('WLPR_PLUGIN_URL') or define('WLPR_PLUGIN_URL', plugin_dir_url(__FILE__));
defined('WLPR_VIEW_PATH') or define('WLPR_VIEW_PATH', str_replace("\\", '/', __DIR__) . '/App/Views');
defined('WLPR_MINIMUM_PHP_VERSION') or define('WLPR_MINIMUM_PHP_VERSION', '7.0.0');
defined('WLPR_MINIMUM_WP_VERSION') or define('WLPR_MINIMUM_WP_VERSION', '4.9');
defined('WLPR_MINIMUM_WC_VERSION') or define('WLPR_MINIMUM_WC_VERSION', '6.5');
defined('WLPR_MINIMUM_WLR_VERSION') or define('WLPR_MINIMUM_WLR_VERSION', '1.4.0');

add_action('admin_init', function () {
	CompatibleCheck::checkDependencies();
});

add_action('plugins_loaded', function () {
	if (!class_exists(Router::class)) {
		return;
	}
	if(CompatibleCheck::checkDependencies()){
		//check for init method in Router class
		$plugin = new Router();
		if (method_exists($plugin, 'init')) {
			$plugin->init();
		}
	}
});
if(class_exists(Setup::class)){
	Setup::init();
}
<?php

namespace WPLoyalty\Migration\App\Helpers;

defined('ABSPATH') or exit;

class PluginHelper
{
    /**
     * Get plugin route path
     */
    public static function pluginRoutePath($isPro = false)
    {
        if ($isPro) {
            return WLMR_PLUGIN_PATH . 'Pro/routes';
        }
        return WLMR_PLUGIN_PATH . 'Core/routes';
    }

    /**
     * Check if plugin is PRO
     */
    public static function isPRO()
    {
        return wlmr_app()->get('is_pro_plugin') === true;
    }

    /**
     * Get plugin URL
     */
    public static function pluginUrl($path = '')
    {
        return WLMR_PLUGIN_URL . $path;
    }

    /**
     * Get plugin path
     */
    public static function pluginPath($path = '')
    {
        return WLMR_PLUGIN_PATH . $path;
    }

    /**
     * Get plugin version
     */
    public static function pluginVersion()
    {
        return WLMR_VERSION;
    }

    /**
     * Get plugin slug
     */
    public static function pluginSlug()
    {
        return WLMR_PLUGIN_SLUG;
    }

    /**
     * Get plugin name
     */
    public static function pluginName()
    {
        return WLMR_PLUGIN_NAME;
    }

    /**
     * Get plugin prefix
     */
    public static function pluginPrefix()
    {
        return WLMR_PREFIX;
    }

    /**
     * Get template override directory path
     */
    public static function templateOverrideDirPath()
    {
        return WLMR_TEMPLATE_OVERRIDE_DIR_PATH;
    }

    /**
     * Get auth routes
     */
    public static function getAuthRoutes()
    {
        return require(static::pluginRoutePath() . '/auth-api.php');
    }

    /**
     * Get guest routes
     */
    public static function getGuestRoutes()
    {
        return require(static::pluginRoutePath() . '/guest-api.php');
    }

    /**
     * Get admin hooks
     */
    public static function getAdminHooks()
    {
        return require(static::pluginRoutePath() . '/admin-hooks.php');
    }

    /**
     * Get WooCommerce hooks
     */
    public static function getWooCommerceHooks()
    {
        return require(static::pluginRoutePath() . '/woocommerce-hooks.php');
    }

    /**
     * Get custom hooks
     */
    public static function getCustomHooks()
    {
        return require(static::pluginRoutePath() . '/custom-hooks.php');
    }

    /**
     * Get WP hooks
     */
    public static function getWPHooks()
    {
        return require(static::pluginRoutePath() . '/wp-hooks.php');
    }
}
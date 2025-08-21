<?php

namespace WPLoyalty\Migration\App\Helpers;

defined('ABSPATH') or exit;

class WordpressHelper
{
    /**
     * Verify nonce
     */
    public static function verifyNonce($nonceKey, $nonce)
    {
        if (empty($nonceKey) || empty($nonce)) {
            return false;
        }

        return wp_verify_nonce($nonce, $nonceKey);
    }

    /**
     * Create nonce
     */
    public static function createNonce($action)
    {
        return wp_create_nonce($action);
    }

    /**
     * Get nonce field
     */
    public static function nonceField($action, $name = '_wpnonce', $referer = true, $echo = true)
    {
        return wp_nonce_field($action, $name, $referer, $echo);
    }

    /**
     * Get nonce URL
     */
    public static function nonceUrl($actionUrl, $action = -1, $name = '_wpnonce')
    {
        return wp_nonce_url($actionUrl, $action, $name);
    }

    /**
     * Check if user can
     */
    public static function currentUserCan($capability, $args = null)
    {
        return current_user_can($capability, $args);
    }

    /**
     * Get current user ID
     */
    public static function getCurrentUserId()
    {
        return get_current_user_id();
    }

    /**
     * Get current user
     */
    public static function getCurrentUser()
    {
        return wp_get_current_user();
    }

    /**
     * Check if user is logged in
     */
    public static function isUserLoggedIn()
    {
        return is_user_logged_in();
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin()
    {
        return current_user_can('manage_options');
    }

    /**
     * Check if user is WooCommerce admin
     */
    public static function isWooCommerceAdmin()
    {
        return current_user_can('manage_woocommerce');
    }

    /**
     * Get admin URL
     */
    public static function adminUrl($path = '', $scheme = 'admin')
    {
        return admin_url($path, $scheme);
    }

    /**
     * Get site URL
     */
    public static function siteUrl($path = '', $scheme = null)
    {
        return site_url($path, $scheme);
    }

    /**
     * Get home URL
     */
    public static function homeUrl($path = '', $scheme = null)
    {
        return home_url($path, $scheme);
    }

    /**
     * Get plugin URL
     */
    public static function pluginUrl($path = '')
    {
        return plugins_url($path, WLMR_PLUGIN_FILE);
    }

    /**
     * Get plugin path
     */
    public static function pluginPath($path = '')
    {
        return plugin_dir_path(WLMR_PLUGIN_FILE) . $path;
    }

    /**
     * Get plugin basename
     */
    public static function pluginBasename()
    {
        return plugin_basename(WLMR_PLUGIN_FILE);
    }

    /**
     * Check if plugin is active
     */
    public static function isPluginActive($plugin)
    {
        return is_plugin_active($plugin);
    }

    /**
     * Check if plugin is active for network
     */
    public static function isPluginActiveForNetwork($plugin)
    {
        return is_plugin_active_for_network($plugin);
    }

    /**
     * Get option
     */
    public static function getOption($option, $default = false)
    {
        return get_option($option, $default);
    }

    /**
     * Update option
     */
    public static function updateOption($option, $value, $autoload = null)
    {
        return update_option($option, $value, $autoload);
    }

    /**
     * Delete option
     */
    public static function deleteOption($option)
    {
        return delete_option($option);
    }

    /**
     * Add action
     */
    public static function addAction($hook_name, $callback, $priority = 10, $accepted_args = 1)
    {
        return add_action($hook_name, $callback, $priority, $accepted_args);
    }

    /**
     * Add filter
     */
    public static function addFilter($hook_name, $callback, $priority = 10, $accepted_args = 1)
    {
        return add_filter($hook_name, $callback, $priority, $accepted_args);
    }

    /**
     * Do action
     */
    public static function doAction($hook_name, ...$arg)
    {
        return do_action($hook_name, ...$arg);
    }

    /**
     * Apply filters
     */
    public static function applyFilters($hook_name, $value, ...$args)
    {
        return apply_filters($hook_name, $value, ...$args);
    }
}
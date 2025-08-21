<?php

namespace RelayWp\Affiliate\Pro\Helpers;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Response;
use Error;
use Exception;
use RelayWp\Affiliate\App\Helpers\PluginHelper;
use RelayWp\Affiliate\Pro\ValidationRequest\ValidateLicenseRequest;

defined("ABSPATH") or exit;

class License
{
    /**
     * License data.
     *
     * @var array
     */
    private static $data;

    /**
     * Remote API base url.
     *
     * @var string
     */
    private static $remote_url = 'https://wprelay.com/wp-json/products/v1';

    /**
     * Plugin details.
     *
     * @var string[]
     */
    private static $plugin = [
        'name' => RWPA_PLUGIN_NAME,
        'file' => RWPA_PLUGIN_FILE,
        'version' => RWPA_VERSION,
        'slug' => 'wprelay-pro',
        'prefix' => 'wprelay_',
        'url' => 'https://wprelay.com/',
        'icon_url' => '',
        'settings_url' => 'admin.php?page=relay-affiliate-marketing#/settings',
        'update_check_period' => 12, // hours
    ];

    /**
     * Init hooks.
     */
    public static function init()
    {
        if (!is_admin()) {
            return;
        }

        global $pagenow;
        $plugin_basename = plugin_basename(self::$plugin['file']);
        add_filter('puc_request_info_result-' . self::$plugin['slug'], [__CLASS__, 'modifyPluginInfo'], 10, 2);
        add_filter('in_plugin_update_message-' . $plugin_basename, [__CLASS__, 'showExpiredLicenseMessage'], 10, 2);

        if (!empty($pagenow) && in_array($pagenow, ['plugins.php', 'plugins-network.php']) && self::getLicenseKey() == '') {
            add_action('admin_notices', [__CLASS__, 'showEnterLicenseKeyNotice']);
        }

        self::runUpdater();
    }

    /**
     * To perform license activation.
     */
    public static function activate($key)
    {
        $result = ['status' => 'failed'];
        $response = self::apiRequest('license/activate', ['key' => $key]);
        if (empty($response)) {
            return $result;
        }

        if (!empty($response['status']) && in_array($response['status'], ['active', 'activated'])) {
            self::updateData([
                'key' => $key,
                'status' => 'active',
                'expires' => !empty($response['expires']) ? $response['expires'] : '',
            ]);
        } else {
            self::updateData(['key' => $key]);
        }
        return $response;
    }

    /**
     * To perform license deactivation.
     */
    public static function deactivate()
    {
        $result = ['status' => 'failed'];
        if (empty(self::getLicenseKey())) {
            return $result;
        }
        $response = self::apiRequest('license/deactivate');
        if (empty($response)) {
            return $result;
        }

        if (!empty($response['status']) && in_array($response['status'], ['inactive', 'deactivated'])) {
            self::deleteData();
        }
        return $response;
    }

    /**
     * To perform license check.
     */
    public static function checkStatus($key)
    {
        $result = ['status' => 'failed'];
        $response = self::apiRequest('license/status', ['key' => $key]);
        if (empty($response)) {
            return $result;
        }
        return $response;
    }

    /**
     * To get license data.
     */
    private static function getData($key = '', $default = false)
    {
        if (!isset(self::$data)) {
            self::$data = get_option(self::$plugin['prefix'] . 'license', []);
        }
        return $key == '' ? self::$data : (self::$data[$key] ?? $default);
    }

    /**
     * Get the license key.
     *
     * @return string
     */
    public static function getLicenseKey()
    {
        return self::getData('key', '');
    }

    /**
     * Get license key status
     *
     * @return string
     */
    public static function getLicenseStatus($format = false)
    {
        $status = self::getData('status', 'inactive');
        if (!$format) {
            return $status;
        }

        switch ($status) {
            case 'active':
                return __('Active', 'relay-affiliate-marketing');
            case 'inactive':
                return __('Inactive', 'relay-affiliate-marketing');
            case 'expired':
                return __('Expired', 'relay-affiliate-marketing');
        }
        return '';
    }

    /**
     * Update license data.
     */
    private static function updateData($data)
    {
        update_option(self::$plugin['prefix'] . 'license', array_merge(self::getData(), $data));
        self::$data = null;
    }

    /**
     * Delete license data.
     */
    private static function deleteData()
    {
        delete_option(self::$plugin['prefix'] . 'license');
        self::$data = null;
    }

    /**
     * To run the updater.
     */
    private static function runUpdater()
    {
        if (!empty(self::getLicenseKey()) && class_exists('Puc_v4_Factory')) {
            $update_url = self::getApiUrl('update');
            \Puc_v4_Factory::buildUpdateChecker($update_url, self::$plugin['file'], self::$plugin['slug'], self::$plugin['update_check_period']);
        }
    }

    /**
     * To prepare API request url.
     */
    private static function getApiUrl($endpoint, $params = [])
    {
        $default_params = [
            'key' => self::getLicenseKey(),
            'slug' => self::$plugin['slug'],
            'version' => self::$plugin['version'],
        ];
        return self::$remote_url . '/' . $endpoint . '?' . http_build_query(array_merge($default_params, $params));
    }

    /**
     * Make API request.
     */
    private static function apiRequest($endpoint, $params = [])
    {
        $url = self::getApiUrl($endpoint, $params);

        $response = wp_remote_get($url);
        if (!empty($response) && !is_wp_error($response) && wp_remote_retrieve_response_code($response) == 200) {
            return json_decode(wp_remote_retrieve_body($response), true);
        }
        return false;
    }

    /**
     * Allow to modify plugin info if needed.
     */
    public static function modifyPluginInfo($plugin_info, $result)
    {
        return $plugin_info;
    }

    /**
     * Message on plugin page when license is expired
     *
     * @param $plugin_data
     * @param $response
     * @return mixed
     */
    public static function showExpiredLicenseMessage($plugin_data, $response)
    {
        if (!empty($response) && empty($response->package)) {
            echo '<br>';
            if (empty(self::getLicenseKey())) {
                $upgrade_url = '<a href="' . admin_url(self::$plugin['settings_url']) . '">' . esc_html__('enter license key', 'relay-affiliate-marketing') . '</a>';
            } else {
                $upgrade_url = '<a target="_blank" href="' . self::$plugin['url'] . '">' . esc_html__('renew your support license', 'relay-affiliate-marketing') . '</a>';
            }

            // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
            /* translators: placeholder description */
            $notice_message = esc_html__('Please %s to receive automatic updates or you can manually update the plugin by downloading it.', 'relay-affiliate-marketing');

            //  phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo sprintf($notice_message, $upgrade_url);
        }
        return $plugin_data;
    }

    /**
     * To display waring message in plugin page while there is no licence key.
     */
    public static function showEnterLicenseKeyNotice()
    {
        $html_prefix = '<div class="notice notice-warning">';
        $message = '<p><strong>' . self::$plugin['name'] . ' - </strong>';
        $message .= __("Make sure to activate your license to receive updates, support and security fixes!", 'relay-affiliate-marketing') . '</p>';
        $message .= '<p>';
        $message .= '<a href="' . admin_url(self::$plugin['settings_url']) . '" class="button-secondary">';
        $message .= __("Enter license key", 'relay-affiliate-marketing') . '</a>';
        $message .= '<a href="' . self::$plugin['url'] . '" target="_blank" class="button-primary" style="margin-left: 12px;">';
        $message .= __("Get License", 'relay-affiliate-marketing') . '</a>';
        $message .= '</p>';
        $html_suffix = '</div>';
        echo wp_kses_post($html_prefix . $message . $html_suffix);
    }

    public static function validateLicense(Request $request)
    {
        $request->validate(new ValidateLicenseRequest());
        try {
            $license_key = $request->get('licence_key');

            $status = License::getLicenseStatus();

            if (in_array($status, ['deactivated', 'inactive'])) {
                $response = License::activate($license_key);
            } else {
                $response = License::deactivate();
            }

            return $response;
        } catch (Error | Exception $exception) {
            PluginHelper::logError('Error Occurred While Processing', [__CLASS__, __FUNCTION__], $exception);
            return Response::error(PluginHelper::serverErrorMessage());
        }
    }


    public static function getLicenceDetails(Request $request)
    {
        try {
            $status = License::getLicenseStatus();
            $key = License::getLicenseKey();

            if ($status == 'activated') {
                $message = __("Licence Activated Successfully", 'relay-affiliate-marketing');
            } else if ($status == 'deactivated') {
                $message = __("Licence Deactivated Successfully", 'relay-affiliate-marketing');
            } else if ($status == 'expired') {
                $message = __("Licence Expired", 'relay-affiliate-marketing');
            } else {
                $message = __("License Key is invalid", 'relay-affiliate-marketing');
            }

            return Response::success([
                'licence_key' => $key,
                'status' => $status,
                'message' => $message,
            ]);
        } catch (Error | Exception $exception) {
            PluginHelper::logError('Error Occurred While Processing', [__CLASS__, __FUNCTION__], $exception);
            return Response::error(PluginHelper::serverErrorMessage());
        }
    }
}

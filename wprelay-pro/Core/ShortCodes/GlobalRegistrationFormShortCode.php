<?php

namespace RelayWp\Affiliate\Core\ShortCodes;

defined('ABSPATH') or exit;

use RelayWp\Affiliate\App\Helpers\AssetHelper;
use RelayWp\Affiliate\App\Helpers\PluginHelper;
use RelayWp\Affiliate\App\Helpers\WordpressHelper;
use RelayWp\Affiliate\App\Hooks\AssetsActions;
use RelayWp\Affiliate\App\Route;
use RelayWp\Affiliate\App\Services\Settings;
use RelayWp\Affiliate\Core\Models\Affiliate;
use RelayWp\Affiliate\Core\Models\Member;

class GlobalRegistrationFormShortCode
{

    public static function register()
    {
        add_shortcode('rwpa_affiliate_go_registration_form', [__CLASS__, 'handleGlobalRegistrationShortCode']);

        //BACKWARD Compatibility before version < 1.0.5
        add_shortcode('affiliate_go_registration_form', [__CLASS__, 'handleGlobalRegistrationShortCode']);
    }

    public static function handleGlobalRegistrationShortCode()
    {

        $enable_registration = Settings::get('affiliate_settings.general.allow_affiliate_registration');
        $plugin_name = RWPA_PLUGIN_NAME;
        if (!$enable_registration) {
            /* translators: placeholder description */
            $notice_html = '<div class="woocommerce-message woocommerce-error">' . vsprintf(esc_html__('Affiliate Registration Not Enabled in %s', 'relay-affiliate-marketing'), [$plugin_name]) . '</div>';
            return $notice_html;
        }

        $site_key = Settings::get('affiliate_settings.recaptcha.site_key');
        $secret_key = Settings::get('affiliate_settings.recaptcha.secret_key');

        if (apply_filters('rwpa_enable_spam_verification_services', true) && (empty($site_key) || empty($secret_key))) {
            /* translators: placeholder description */
            $notice_html = '<div class="woocommerce-message woocommerce-error">' . vsprintf(esc_html__('Google Recaptcha Keys are not configured in %s', 'relay-affiliate-marketing'), [$plugin_name]) . '</div>';
            return $notice_html;
        }

        $email = '';
        $firstName = '';
        $lastName = '';

        if (is_user_logged_in()) {
            global $current_user;
            $email = $current_user->user_email;
            $firstName = $current_user->user_firstname;
            $lastName = $current_user->user_lastname;

            $affiliate = Member::query()->where("email = %s AND type = %s", [$email, 'affiliate'])->first();

            if (!empty($affiliate) && apply_filters('rwpa_wprelay_hide_registration_form_for_registered_accounts', true)) {
                /* translators: placeholder description */
                $notice_html = '<div class="woocommerce-message woocommerce-error">' . vsprintf(esc_html__('Email Already Registered as affiliate %s', 'relay-affiliate-marketing'), [$email]) . '</div>';
                return $notice_html;
            }
        }

        $pluginSlug = RWPA_PLUGIN_SLUG;
        $registrationScriptHandle = "{$pluginSlug}-registration-script";
        $registrationHandle = "{$pluginSlug}-registration";
        $storeConfig = AssetsActions::getStoreConfigValues();
        $isPro = PluginHelper::isPRO();

        $resourcePath = AssetHelper::getResourceURL();
        wp_enqueue_script($registrationScriptHandle, "{$resourcePath}/scripts/registration.js", array('jquery'), RWPA_VERSION, true);
        wp_enqueue_style($registrationHandle, "{$resourcePath}/css/registration.css", [], RWPA_VERSION);
        wp_localize_script($registrationScriptHandle, 'rwpa_relay_store', $storeConfig);
        $site_key = Settings::get('affiliate_settings.recaptcha.site_key');

        if (!empty($site_key)) {
            wp_enqueue_script('google-recaptcha', "https://www.google.com/recaptcha/api.js?render={$site_key}", array(), RWPA_VERSION, true);
        }


        if (function_exists('WC')) {
            $countries = WC()->countries->get_countries();
        } else {
            $countries = [];
        }

        $countries = apply_filters('rwpa_wprelay_get_countries_to_show_for_registration', $countries);
        $countries = apply_filters('wprelay_get_countries_to_show_for_registration', $countries);
        $default_country_code = apply_filters('rwpa_wprelay_get_default_country_code_for_registration', null);
        $default_country_code = apply_filters('wprelay_get_default_country_code_for_registration', $default_country_code);

        $nonce = [
            '_wp_nonce_key' => 'affiliate_registration_nonce',
            '_wp_nonce' => WordpressHelper::createNonce('affiliate_registration_nonce')
        ];

        $actionName = is_user_logged_in() ? Route::AJAX_NAME : Route::AJAX_NO_PRIV_NAME;
        $colors = Settings::get('general_settings.color_settings');

        if (!is_array($countries) || empty($countries)) {
            $notice_html = '<div class="woocommerce-message woocommerce-error">' . esc_html("Countries data is empty - {$plugin_name}") . '</div>';
            return $notice_html;
        }

	    $affiliate_success_message = apply_filters( 'relay_affiliate_registration_form_success_message',"<p class='wprelay-text-color'>".__( "Thank you for your interest in partnering with us as an affiliate. We're thrilled to receive your request and appreciate the opportunity to explore this collaboration together", 'relay-affiliate-marketing' )."</p>
		    <p class='wprelay-text-color'>".__( "Your affiliate application has been successfully captured by our team. We are currently reviewing your details to ensure the best fit for our affiliate program. Rest assured, we'll get back to you promptly with further instructions and next steps", 'relay-affiliate-marketing' )."</p>"
	    );

        $all_data = [
			'email' => $email,
	        'firstName' => $firstName,
	        'lastName' => $lastName,
	        'default_country_code' => $default_country_code,
	        'countries' => $countries,
	        'nonce' => $nonce,
	        'actionName' => $actionName,
	        'colors' => $colors,
	        'affiliate_success_message' => $affiliate_success_message,
        ];

        $path = RWPA_PLUGIN_PATH . 'resources/pages/';

		wc_get_template('registration.php',$all_data,RWPA_TEMPLATE_OVERRIDE_DIR_PATH,$path);
//        ob_start(); // Start output buffering
//        include $path . '/registration.php'; // Include the PHP file
//        return ob_get_clean();
    }
}

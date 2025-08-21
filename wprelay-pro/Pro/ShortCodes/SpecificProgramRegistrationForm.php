<?php

namespace RelayWp\Affiliate\Pro\ShortCodes;

defined('ABSPATH') or exit;

use RelayWp\Affiliate\App\Helpers\AssetHelper;
use RelayWp\Affiliate\App\Helpers\PluginHelper;
use RelayWp\Affiliate\App\Helpers\WordpressHelper;
use RelayWp\Affiliate\App\Hooks\AssetsActions;
use RelayWp\Affiliate\App\Route;
use RelayWp\Affiliate\App\Services\Settings;
use RelayWp\Affiliate\Core\Models\Member;
use RelayWp\Affiliate\Core\Models\Program;
use RelayWp\Affiliate\Pro\Models\Program as ProgramPro;

class SpecificProgramRegistrationForm
{

    public static function register()
    {
        add_shortcode('rwpa_wprelay_registration_with_custom_fields', [__CLASS__, 'handlSpecificProgramRegistrationShortcode']);
        //BACKWARD Compatibility before version < 1.0.5
        add_shortcode('wprelay_registration_with_custom_fields', [__CLASS__, 'handlSpecificProgramRegistrationShortcode']);
    }

    public static function handlSpecificProgramRegistrationShortcode($attr)
    {
        $plugin_name = RWPA_PLUGIN_NAME;

        if (!empty($notice = static::canShortCodeViewable($attr))) {
            return $notice;
        }

        $program_id = $attr['program_id'];
        $program_slug = $attr['slug'];

        $program = Program::query()->where("id = %d AND custom_field_shortcode = %s", [$program_id, $program_slug])
            ->first();

        if (empty($program)) {
            $notice_html = '<div class="woocommerce-message woocommerce-error">' . esc_html("ShortCode Attributes Values does not match Any Programs. {$plugin_name}") . '</div>';
            return $notice_html;
        }

        $pluginSlug = RWPA_PLUGIN_SLUG;
        $registrationScriptHandle = "{$pluginSlug}-program-registration-script";
        $registrationHandle = "{$pluginSlug}-program-registration";
        $storeConfig = AssetsActions::getStoreConfigValues();
        $isPro = PluginHelper::isPRO();

        $resourcePath = AssetHelper::getResourceURL();
        $js_file = RWPA_PLUGIN_URL . '/Pro/views/scripts/program-registration.js';
        $css_file = RWPA_PLUGIN_URL . '/Pro/views/css/program-registration.css';
        wp_enqueue_script($registrationScriptHandle, "{$js_file}", array('jquery'), RWPA_VERSION, true);
        wp_enqueue_style($registrationHandle, "{$css_file}", [], RWPA_VERSION);
        wp_localize_script($registrationScriptHandle, 'rwpa_relay_store', $storeConfig);
        $site_key = Settings::get('affiliate_settings.recaptcha.site_key');

        if (!empty($site_key)) {
            wp_enqueue_script('google-recaptcha', "https://www.google.com/recaptcha/api.js?render={$site_key}", array(), RWPA_VERSION, true);
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

            if (!empty($affiliate) && apply_filters('wprelay_hide_registration_form_for_registered_accounts', true)) {
                $notice_html = '<div class="woocommerce-message woocommerce-error">' . vsprintf(esc_html__('Email Already Registered as affiliate %s', 'wprelay'), [$email]) . '</div>';
                return $notice_html;
            }
        }

        $nonce = [
            '_wp_nonce_key' => 'affiliate_registration_nonce',
            '_wp_nonce' => WordpressHelper::createNonce('affiliate_registration_nonce')
        ];

        $rendering_details = ProgramPro::getFormDataForRendering($program);

        $actionName = is_user_logged_in() ? Route::AJAX_NAME : Route::AJAX_NO_PRIV_NAME;
        $colors = Settings::get('general_settings.color_settings');

	    $all_data = [
		    'email' => $email,
		    'firstName' => $firstName,
		    'lastName' => $lastName,
		    'rendering_details' => $rendering_details,
		    'nonce' => $nonce,
		    'actionName' => $actionName,
		    'program' => $program,
		    'colors' => $colors,
	    ];

	    $path = RWPA_PLUGIN_PATH . 'Pro/views/';

	    wc_get_template('program-based-registration.php',$all_data,RWPA_TEMPLATE_OVERRIDE_DIR_PATH,$path);

//        ob_start(); // Start output buffering
//        include $path . 'program-based-registration.php'; // Include the PHP file
//        return ob_get_clean();
    }


    public static function canShortCodeViewable($attr)
    {
        $plugin_name = RWPA_PLUGIN_NAME;

        $site_key = Settings::get('affiliate_settings.recaptcha.site_key');
        $secret_key = Settings::get('affiliate_settings.recaptcha.secret_key');

        if (apply_filters('rwpa_enable_spam_verification_services', true) && (empty($site_key) || empty($secret_key))) {
            /* translators: placeholder description */
            $notice_html = '<div class="woocommerce-message woocommerce-error">' . vsprintf(esc_html__('Google Recaptcha Keys are not configured in %s', 'relay-affiliate-marketing'), [$plugin_name]) . '</div>';
            return $notice_html;
        }

        if (empty($attr['program_id']) || empty($attr['slug'])) {
            $notice_html = '<div class="woocommerce-message woocommerce-error">' . esc_html("ShortCode Missing the Required Attributes {$plugin_name}") . '</div>';
            return $notice_html;
        }

        return false;
    }
}

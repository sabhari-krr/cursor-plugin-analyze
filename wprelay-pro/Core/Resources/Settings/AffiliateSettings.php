<?php

namespace RelayWp\Affiliate\Core\Resources\Settings;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\Core\Models\Program;
use RelayWp\Affiliate\App\Resource;


class AffiliateSettings extends Resource
{

    public function toArray($settings)
    {
        $programInfo = null;

        if (isset($settings['general']['default_program_id']) && Functions::getBoolValue($settings['general']['default_program_id'])) {
            $programInfo =  [
                'label' => Program::find($settings['general']['default_program_id'])->title,
                'value' => $settings['general']['default_program_id']
            ];
        }

        return [
            'general' => [
                'allow_affiliate_registration' => (bool)($settings['general']['allow_affiliate_registration'] ?? false),
                'affiliate_registration_page_id' => $settings['general']['affiliate_registration_page_id'] ?? '',
                'short_code_name' => "[rwpa_affiliate_go_registration_form]",
                'auto_approve_commission' => (bool)$settings['general']['auto_approve_commission'] ?? false,
                'auto_approve_delay_in_days' => $settings['general']['auto_approve_delay_in_days'] ?? 0,
                'default_program_id' => $settings['general']['default_program_id'] ?? null,
                'default_program' => $programInfo,
            ],
            'recaptcha' => [
                'site_key' => $settings['recaptcha']['site_key'] ?? '',
                'secret_key' => $settings['recaptcha']['secret_key'] ?? '',
            ],
            'successful_order_status' => $settings['successful_order_status'] ?? [],
            'failure_order_status' => $settings['failure_order_status'] ?? [],
            'url_options' => [
                'url_variable' => $settings['url_options']['url_variable'] ?? 'aff',
                'referral_code_value' => $settings['url_options']['referral_code_value'] ?? 'random',
            ],
        ];
    }
}

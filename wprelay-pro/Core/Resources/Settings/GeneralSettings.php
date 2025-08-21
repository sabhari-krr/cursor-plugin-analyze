<?php

namespace RelayWp\Affiliate\Core\Resources\Settings;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Resource;

class GeneralSettings extends Resource
{

    public function toArray($settings)
    {

        return [
            'cookie_duration' => $settings['cookie_duration'],
            'commission_settings' => [
                'exclude_shipping' => (bool)($settings['commission_settings']['exclude_shipping'] ?? false),
                'exclude_taxes' => (bool)($settings['commission_settings']['exclude_taxes'] ?? false),
            ],
            'contact_information' => [
                'merchant_name' => $settings['contact_information']['merchant_name'] ?? '',
                'merchant_email' => $settings['contact_information']['merchant_email'] ?? '',
            ],

            'color_settings' => [
                'primary_color' => $settings['color_settings']['primary_color'] ?? '#000000',
                'secondary_color' => $settings['color_settings']['secondary_color'] ?? '#ffffff',
            ]

        ];
    }
}


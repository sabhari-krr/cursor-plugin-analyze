<?php

namespace RelayWp\Affiliate\Core\ValidationRequest\Settings;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;

class GeneralSettingsRequest implements FormRequest
{
    public function rules(Request $request)
    {
        $rules = [
            'cookie_duration' => ['required', 'numeric'],
            'commission_settings' => ['required', 'array'],
            'color_settings' => ['required', 'array'],
            'color_settings.primary_color' => ['required'],
            'color_settings.secondary_color' => ['required'],
            'commission_settings.exclude_shipping' => ['optional'],
            'commission_settings.exclude_taxes' => ['optional'],

            'contact_information' => ['required', 'array'],
            'contact_information.merchant_email' => ['required', 'email'],
            'contact_information.merchant_name' => ['required'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'cookie_duration.required' => __('Cookie duration is required', 'relay-affiliate-marketing'),
            'cookie_duration.numeric' => __('Expected value is numeric', 'relay-affiliate-marketing'),

            'contact_information.contact_information.merchant_name.required' => __('Merchant Name is required', 'relay-affiliate-marketing'),
            'contact_information.merchant_email.required' => __('Merchant Email is required', 'relay-affiliate-marketing'),
            'contact_information.merchant_email.email' => __('Not a valid email', 'relay-affiliate-marketing'),

            'color_settings.primary_color.required' => __('Primary Color is required', 'relay-affiliate-marketing'),
            'color_settings.secondary_color.required' => __('Secondary Color is required', 'relay-affiliate-marketing'),
        ];
    }
}

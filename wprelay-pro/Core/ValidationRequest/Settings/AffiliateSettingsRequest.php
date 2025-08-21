<?php

namespace RelayWp\Affiliate\Core\ValidationRequest\Settings;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;

class AffiliateSettingsRequest implements FormRequest
{
    public function rules(Request $request)
    {
        $rules = [
            'general' => ['required', 'array'],
            'general.allow_affiliate_registration' => ['optional', 'bool_type'],
            'general.auto_approve_commission' => ['optional', 'bool_type'],
            'url_options' => ['required', 'array'],
            'url_options.url_variable' => ['required', 'string_rwt'],
            'failure_order_status' => ['required', 'array'],
            'successful_order_status' => ['required', 'array'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            /* translators: placeholder description */
            'url_options.url_variable.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Url Variable', 'relay-affiliate-marketing')]),
            'url_options.url_variable.string_rwt' => __('Not a valid string', 'relay-affiliate-marketing'),
            /* translators: placeholder description */
            'successful_order_status.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Successful Order Status', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'failure_order_status.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Failure Order Status', 'relay-affiliate-marketing')]),
        ];
    }
}

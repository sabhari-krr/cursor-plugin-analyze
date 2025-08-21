<?php

namespace RelayWp\Affiliate\Core\ValidationRequest\Affiliate;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\WC;
use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;
use RelayWp\Affiliate\Core\Models\Affiliate;
use RelayWp\Affiliate\Core\Models\Member;

class AffiliateRequest implements FormRequest
{
    public function rules(Request $request)
    {
        $rules = [
            'first_name' => ['required'],
            //            'code' => ['required', 'string_rwt', ['uniqueColumn', Affiliate::getTableName(), 'referral_code'], 'is_coupon_exists'],
            'program_id' => ['required', 'numeric'],
            'last_name' => ['required'],
            'email' => ['required', 'email', 'is_affiliate_email_already_exists'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            /* translators: placeholder description */
            'first_name.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('First Name', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'last_name.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Last Name', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'phone_number.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Phone Number', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'program_id.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Program', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'program_id.numeric' => __('Expected value is numeric', 'relay-affiliate-marketing'),
            /* translators: placeholder description */
            'email.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Email', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'email.email' => vsprintf(esc_html__('Not a valid %s', 'relay-affiliate-marketing'), [__('Email', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'email.is_affiliate_email_already_exists' => vsprintf(esc_html__('%1$s already exists as %1$s', 'relay-affiliate-marketing'), [__('Email', 'relay-affiliate-marketing'), __('Affiliate', 'relay-affiliate-marketing')]),
        ];
    }
}

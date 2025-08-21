<?php

namespace RelayWp\Affiliate\Core\ValidationRequest\Affiliate\StoreFront;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;
use RelayWp\Affiliate\Core\Models\Affiliate;
use RelayWp\Affiliate\Core\Models\Member;

class AffiliateUpdateRequest implements FormRequest
{
    public function rules(Request $request)
    {
        $data = $request->all();

        $rules = [
            'affiliate_id' => ['required', 'numeric'],
            'first_name' => ['required'],
            'last_name' => ['required'],
            'billing_email' => ['optional', 'email'],
            'phone_number' => ['required'],
            'address' => ['required'],
            'country' => ['required'],
            'zip_code' => ['required'],
            'city' => ['required'],
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
            'address.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Address', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'city.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('City', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'zip_code.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Zip code', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'email.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Email', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'email.email' => vsprintf(esc_html__('Not a valid %s', 'relay-affiliate-marketing'), [__('Email', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'email.is_affiliate_email_already_exists' => vsprintf(esc_html__('%1$s already exists as %1$s', 'relay-affiliate-marketing'), [__('Email', 'relay-affiliate-marketing'), __('Affiliate', 'relay-affiliate-marketing')]),
        ];
    }
}

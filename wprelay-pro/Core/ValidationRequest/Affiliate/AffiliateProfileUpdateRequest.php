<?php

namespace RelayWp\Affiliate\Core\ValidationRequest\Affiliate;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;
use RelayWp\Affiliate\Core\Models\Affiliate;

class AffiliateProfileUpdateRequest implements FormRequest
{
    public function rules(Request $request)
    {
        $data = $request->all();

        if (isset($data['affiliate_id'])) {
            $affiliate = Affiliate::query()->findOrFail($data['affiliate_id']);
            $uniqueValidationRule[] = 'id';
            $uniqueValidationRule[] = $affiliate->member_id;
        }

        $rules = [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'billing_email' => ['optional', 'email'],
            'member_id' => ['required', 'numeric'],
            'affiliate_id' => ['required', 'numeric'],
            'phone_number' => ['required'],
            'social_links.facebook' => ['optional', 'url'],
            'social_links.youtube' => ['optional', 'url'],
            'social_links.instagram' => ['optional', 'url'],
            'social_links.twitter' => ['optional', 'url'],
            'social_links.linkedin' => ['optional', 'url'],
            'social_links.web' => ['optional', 'url'],
            'shipping_address' => ['required', 'array'],
            'shipping_address.address' => ['required'],
            'shipping_address.city' => ['required'],
            'shipping_address.zip_code' => ['required'],
            'shipping_address.country' => ['required'],
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
            'billing_email.email' => vsprintf(esc_html__('Not a valid %s', 'relay-affiliate-marketing'), [__('Email', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'email.is_affiliate_email_already_exists' => vsprintf(esc_html__('%1$s already exists as %2$s', 'relay-affiliate-marketing'), [__('Email', 'relay-affiliate-marketing'), __('Affiliate', 'relay-affiliate-marketing')]),

            /* translators: placeholder description */
            'shipping_address.address.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Address', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'shipping_address.city.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('City', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'shipping_address.zip_code.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Zip code', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'shipping_address.country.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Country', 'relay-affiliate-marketing')]),
            //social links

            /* translators: placeholder description */
            'social_links.twitter.url' => vsprintf(esc_html__('%s is not valid', 'relay-affiliate-marketing'), [__('URL', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'social_links.facebook.url' => vsprintf(esc_html__('%s is not valid', 'relay-affiliate-marketing'), [__('URL', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'social_links.youtube.url' => vsprintf(esc_html__('%s is not valid', 'relay-affiliate-marketing'), [__('URL', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'social_links.instagram.url' => vsprintf(esc_html__('%s is not valid', 'relay-affiliate-marketing'), [__('URL', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'social_links.linkedin.url' => vsprintf(esc_html__('%s is not valid', 'relay-affiliate-marketing'), [__('URL', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'social_links.web.url' => vsprintf(esc_html__('%s is not valid', 'relay-affiliate-marketing'), [__('URL', 'relay-affiliate-marketing')]),
        ];
    }
}

<?php

namespace RelayWp\Affiliate\Core\ValidationRequest\Affiliate;

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

        $uniqueValidationRule = ['uniqueColumn', Member::getTableName(), 'email'];
        $uniqueCodeValidationRule = ['uniqueColumn', Affiliate::getTableName(), 'referral_code'];

        if (isset($data['affiliate_id'])) {
            $affiliate = Affiliate::query()->findOrFail($data['affiliate_id']);
            $uniqueValidationRule[] = 'id';
            $uniqueValidationRule[] = $affiliate->member_id;

            $uniqueCodeValidationRule[] = 'id';
            $uniqueCodeValidationRule[] = $affiliate->id;
        }

        $rules = [
            'affiliate_id' => ['required', 'numeric'],
            'code' => ['required', 'string_rwt', $uniqueCodeValidationRule],
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email', $uniqueValidationRule],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            /* translators: placeholder description */
            'first_name.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('First Name', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'code.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Code', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'last_name.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Last Name', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'email.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Email', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'email.email' => vsprintf(esc_html__('Not a valid %s', 'relay-affiliate-marketing'), [__('Email', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'email.is_affiliate_email_already_exists' => vsprintf(esc_html__('%1$s already exists as %2$s', 'relay-affiliate-marketing'), [__('Email', 'relay-affiliate-marketing'), __('Affiliate', 'relay-affiliate-marketing')]),
        ];
    }
}

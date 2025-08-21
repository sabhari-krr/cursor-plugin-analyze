<?php

namespace RelayWp\Affiliate\Pro\ValidationRequest\Rules;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;
use RelayWp\Affiliate\Core\Models\Bonus;
use RelayWp\Affiliate\Core\Models\Program;
use RelayWp\Affiliate\Core\Models\Rules;

class RulesRequest implements FormRequest
{
    public function rules(Request $request)
    {
        $data = $request->all();

        $rules = [
            'title' => ['required', ['lengthBetween', 5, 256]],
            'affiliates_type' => ['required'],
            'type' => ['required', ['in', ['fixed', 'percentage']]],
            'based_on' => ['required', ['in', ['all_products', 'product_in_list', 'product_not_in_list', 'category_in_list', 'category_not_in_list']]],
            'value' => ['required'],
        ];

        if (!empty($data['start_date']) && empty($data['end_date'])) {
            $rules['start_date'] = ['required', ['dateFormat', 'Y-m-d H:i']];
        } else if (!empty($data['start_date']) && !empty($data['end_date'])) {
            $rules['start_date'] = ['required', ['dateFormat', 'Y-m-d H:i']];
            $rules['end_date'] = ['required', ['dateFormat', 'Y-m-d H:i'], ['dateAfter', $data['start_date']]];
        }

        if (!empty($data['based_on']) && ($data['based_on'] == 'product_in_list' || $data['based_on'] == 'product_not_in_list')) {
            $rules['product_ids'] = ['required', 'array'];
        }

        if (!empty($data['based_on']) && ($data['based_on'] == 'category_in_list' || $data['based_on'] == 'category_not_in_list')) {
            $rules['category_ids'] = ['required', 'array'];
        }

        if ($data['affiliates_type'] == 'specific') {
            $rules['affiliate_ids'] = ['required', 'array'];
        }

        return $rules;
    }

    public function messages(): array
    {
        $rules = [
            'title.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Title', 'relay-affiliate-marketing')]),
            'affiliates_type.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Affiliates Type', 'relay-affiliate-marketing')]),
            'type.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Type', 'relay-affiliate-marketing')]),
            'based_on.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Based On', 'relay-affiliate-marketing')]),
            'value.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Value', 'relay-affiliate-marketing')]),
            'start_date.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Start Date', 'relay-affiliate-marketing')]),
            'end_date.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('End Date', 'relay-affiliate-marketing')]),
            'end_date.dateAfter' => vsprintf(esc_html__('%1$s must be greater than %2$s', 'relay-affiliate-marketing'), [__('End Date', 'relay-affiliate-marketing'), __('Start Date', 'relay-affiliate-marketing')]),
            'affiliate_ids.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Affiliate', 'relay-affiliate-marketing')]),
            'products_ids.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Products', 'relay-affiliate-marketing')]),
            'category_ids.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Categories', 'relay-affiliate-marketing')]),
        ];

        return $rules;
    }
}

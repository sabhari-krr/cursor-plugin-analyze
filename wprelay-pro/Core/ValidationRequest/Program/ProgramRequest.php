<?php

namespace RelayWp\Affiliate\Core\ValidationRequest\Program;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;
use RelayWp\Affiliate\Core\Models\Program;

class ProgramRequest implements FormRequest
{
    public $data = [];

    public function rules(Request $request)
    {
        $this->data = $request->all();
        $data = $this->data;


        $uniqueValidationRule = ['uniqueColumn', Program::getTableName(), 'title'];

        if (isset($data['program_id'])) {
            $uniqueValidationRule[] = 'id';
            $uniqueValidationRule[] = $data['program_id'];
        }

        $rules = [
            'title' => ['required', $uniqueValidationRule, ['lengthBetween', 5, 256]],
            'is_active' => ['optional', 'bool_type'],
            'auto_approve' => ['optional', 'bool_type'],
            'commission_type' => ['required', ['in', ['simple', 'advanced', 'rule_based']]],
            'customer_discount_type' => ['required', ['in', ['fixed_cart', 'percent', 'fixed_product', 'no_discount']]],
            'customer_discount_options.min_requirements' => ['required'],
            'customer_discount_options.usage_limits' => ['required'],
        ];

        if (!empty($data['commission_type']) && $data['commission_type'] != 'rule_based') {
            $rules['commission_sub_type'] = ['required', ['in', ['fixed', 'percentage_per_sale', 'no_commission', 'tier_based']]];
        }

        if (!empty($data['commission_type']) && $data['commission_type'] != 'rule_based' && !empty($data['commission_sub_type']) && $data['commission_sub_type'] != 'no_commission') {
            $rules['commission_type_options'] = ['required'];
        }

        if (!empty($data['commission_type']) && $data['commission_type'] != 'rule_based' && !empty($data['commission_sub_type']) && !in_array($data['commission_sub_type'], ['no_commission', 'tier_based'])) {
            $rules['commission_type_options.value'] = ['required', 'numeric', ['min', 1]];
        }


        if (!empty($data['start_date']) && empty($data['end_date'])) {
            $rules['start_date'] = ['required', ['dateFormat', 'Y-m-d H:i']];
        } else if (!empty($data['start_date']) && !empty($data['end_date'])) {
            $rules['start_date'] = ['required', ['dateFormat', 'Y-m-d H:i']];
            $rules['end_date'] = ['required', ['dateFormat', 'Y-m-d H:i'], ['dateAfter', $data['start_date']]];
        }

        if (!empty($data['customer_discount_type']) && $data['customer_discount_type'] != 'no_discount') {
            $rules['customer_discount_options'] = ['required'];
        }

        if (!empty($data['customer_discount_type']) && $data['customer_discount_type'] != 'no_discount') {
            $rules['customer_discount_options.value'] = ['required', 'numeric', ['min', 1]];

            //customer discount options
            if (!empty($data['customer_discount_options']['min_requirements']) && $data['customer_discount_options']['min_requirements']['enabled']) {
                $rules['customer_discount_options.min_requirements.minimum_spend'] = ['optional', 'numeric'];
                if (!empty($data['customer_discount_options']['min_requirements']['minimum_spend']) && ($minimum_spend = $data['customer_discount_options']['min_requirements']['minimum_spend'])) {
                    if (!empty($data['customer_discount_options']['min_requirements']['maximum_spend']))
                        $rules['customer_discount_options.min_requirements.maximum_spend'] = ['optional', 'numeric', ['min', $minimum_spend]];
                }

                $rules['customer_discount_options.min_requirements.individual_use'] = ['optional', 'bool_type'];
                $rules['customer_discount_options.min_requirements.exclude_sale_items'] = ['optional', 'bool_type'];
                $rules['customer_discount_options.min_requirements.products'] = ['optional', 'array'];
                $rules['customer_discount_options.min_requirements.exclude_products'] = ['optional', 'array'];

                //            if (!empty($data['customer_discount_options']['min_requirements']['products'])) {
                //                $product_ids = $data['customer_discount_options']['min_requirements']['products'];
                //                foreach ($product_ids as $index => $product_id) {
                //                    $rules["customer_discount_options.min_requirements.products.{$index}"] = ['required', 'numeric'];
                //                }
                //            }

                //            if (!empty($data['customer_discount_options']['min_requirements']['products'])) {
                //                $exclude_product_ids = $data['customer_discount_options']['min_requirements']['exclude_products'];
                //                foreach ($exclude_product_ids as $index => $product_id) {
                //                    $rules["customer_discount_options.min_requirements.exclude_products.{$index}"] = ['required', 'numeric'];
                //                }
                //            }

                $rules['customer_discount_options.min_requirements.categories'] = ['optional', 'array'];
                $rules['customer_discount_options.min_requirements.exclude_categories'] = ['optional', 'array'];
            }

            if (!empty($data['customer_discount_options']['usage_limits']) && $data['customer_discount_options']['usage_limits']['enabled']) {
                $rules['customer_discount_options.usage_limits.usage_limit_per_user'] = ['optional', 'numeric', ['min', 1]];
            }
        }


        return apply_filters('rwpa_create_program_validation_rules', $rules, $data);
    }

    public function messages(): array
    {
        //messages
        $messages = [
            /* translators: placeholder description */
            'title.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Title', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'commission_type.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Commission Type', 'relay-affiliate-marketing')]),
            'commission_type.in' => __('Invalid value', 'relay-affiliate-marketing'),
            /* translators: placeholder description */
            'commission_sub_type.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Commission Sub Type', 'relay-affiliate-marketing')]),
            'commission_sub_type.in' => __('Invalid value', 'relay-affiliate-marketing'),
            /* translators: placeholder description */
            'commission_type_options.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Commission Type Options', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'commission_type_options.value.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Value', 'relay-affiliate-marketing')]),
            'commission_type_options.value.numeric' => __('Expected value is numeric', 'relay-affiliate-marketing'),
            'commission_type_options.value.min' => __('Minimum value should be 1', 'relay-affiliate-marketing'),

            /* translators: placeholder description */
            'start_date.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Start Date', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'end_date.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('End Date', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'end_date.dateAfter' => vsprintf(esc_html__('%1$s must be greater than %2$s', 'relay-affiliate-marketing'), [__('End Date', 'relay-affiliate-marketing'), __('Start Date', 'relay-affiliate-marketing')]),

            /* translators: placeholder description */
            'customer_discount_type.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Customer Discount Type', 'relay-affiliate-marketing')]),

            'customer_discount_type.in' => __('Invalid value', 'relay-affiliate-marketing'),
            /* translators: placeholder description */
            'customer_discount_options.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Customer Discount Options', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'customer_discount_options.min_requirements.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Minimum Requirements', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'customer_discount_options.usage_limits.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Limits', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'customer_discount_options.min_requirements.maximum_spend.min' => vsprintf(esc_html__('%1$s must be greater than %1$s', 'relay-affiliate-marketing'), [__('Maximum spend', 'relay-affiliate-marketing'), __('Minimum Spend', 'relay-affiliate-marketing')]),
        ];

        return apply_filters('rwpa_create_program_validation_messages', $messages, $this->data);
    }
}

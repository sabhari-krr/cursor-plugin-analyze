<?php

namespace RelayWp\Affiliate\Pro\ValidationRequest;

defined("ABSPATH") or exit;

class ProgramRequest
{

    public static function addCreateRules($rules, $data)
    {
        if (isset($data['commission_type']) && $data['commission_type'] == 'advanced' && !empty($data['commission_sub_type']) && $data['commission_sub_type'] == 'tier_based') {
            $rules['commission_type_options.tier_based_options'] = ['required', 'array'];
            $rules['commission_type_options.tier_based_options.based_on'] = ['required', ['in', ['total_sales_amount', 'number_of_sales_count', 'number_of_referrals']]];
            $rules['commission_type_options.tier_based_options.type'] = ['required', ['in', ['fixed', 'percentage']]];

            if (isset($data['commission_type_options']['tier_based_options']['ranges'])) {
                $ranges = $data['commission_type_options']['tier_based_options']['ranges'];

                foreach ($ranges as $index => $range) {
                    $rules["commission_type_options.tier_based_options.ranges.{$index}"] = ['required', 'array'];
                    //                    $rules["commission_type_options.tier_based_options.ranges.{$index}.type"] = ['required', ['in', ['percentage', 'fixed']]];
                    if (isset($data['commission_type_options']['tier_based_options']['based_on']) && $data['commission_type_options']['tier_based_options']['based_on'] == 'total_sales_amount') {
                        $rules["commission_type_options.tier_based_options.ranges.{$index}.currency"] = ['required', 'array'];
                        $rules["commission_type_options.tier_based_options.ranges.{$index}.currency.label"] = ['required'];
                        $rules["commission_type_options.tier_based_options.ranges.{$index}.currency.value"] = ['required'];
                    }

                    $rules["commission_type_options.tier_based_options.ranges.{$index}.condition"] = ['required', 'numeric', ['min', 1]];
                    $rules["commission_type_options.tier_based_options.ranges.{$index}.value"] = ['required', 'numeric', ['min', 1]];
                }
            }
        }

        return $rules;
    }

    public static function addProgramRequestMessages($messages, $data)
    {
        $messages = array_merge($messages, [
            'commission_type_options.tier_based_options.based_on.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Based On', 'relay-affiliate-marketing')]),
            'commission_type_options.tier_based_options.type.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Type', 'relay-affiliate-marketing')]),
        ]);

        if (isset($data['commission_type_options']['tier_based_options']['ranges'])) {
            $ranges = $data['commission_type_options']['tier_based_options']['ranges'];

            foreach ($ranges as $index => $range) {
                $messages["commission_type_options.tier_based_options.ranges.{$index}.required"] = vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('This Field', 'relay-affiliate-marketing')]);
                if (isset($data['commission_type_options']['tier_based_options']['based_on']) && $data['commission_type_options']['tier_based_options']['based_on'] == 'total_sales_amount') {
                    $messages["commission_type_options.tier_based_options.ranges.{$index}.currency.required"] = vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Currency', 'relay-affiliate-marketing')]);
                    $messages["commission_type_options.tier_based_options.ranges.{$index}.currency.label.required"] = vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Currency Label', 'relay-affiliate-marketing')]);
                    $messages["commission_type_options.tier_based_options.ranges.{$index}.currency.value.required"] = vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Currency Value', 'relay-affiliate-marketing')]);
                }

                $messages["commission_type_options.tier_based_options.ranges.{$index}.condition.required"] = vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Condition', 'relay-affiliate-marketing')]);
                $messages["commission_type_options.tier_based_options.ranges.{$index}.value.required"] = vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Value', 'relay-affiliate-marketing')]);
            }
        }

        return $messages;
    }
}

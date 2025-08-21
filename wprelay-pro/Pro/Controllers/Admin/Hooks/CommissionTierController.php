<?php

namespace RelayWp\Affiliate\Pro\Controllers\Admin\Hooks;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\PluginHelper;
use RelayWp\Affiliate\App\Helpers\WC;
use Cartrabbit\Request\Request;
use RelayWp\Affiliate\Core\Models\Affiliate;
use RelayWp\Affiliate\Core\Models\Customer;
use RelayWp\Affiliate\Core\Models\Order;

class CommissionTierController
{

    /**
     * @group pro
     */
    public static function getProRecursiveData($commissionTierData, Request $request)
    {
        $commissionTierData['recurring_commission_enabled'] = Functions::getBoolValue($request->get('is_recurring_commission_enabled'));
        $commissionTierData['recurring_commission_options'] = $commissionTierData['recurring_commission_enabled'] ? static::recurringOptionsFromRequest($request) : null;

        return $commissionTierData;
    }


    public static function recurringOptionsFromRequest($request, $json = true)
    {
        $recurringOptions = [];

        $recurringOptions['type'] = $request->get('recurring_commission_options.type');

        if ($recurringOptions['type'] == 'interval') {
            $recurringOptions['value'] = $request->get('recurring_commission_options.value');
        }

        if ($json) {
            return wp_json_encode($recurringOptions);
        }

        return $recurringOptions;
    }

    /**
     * @group pro
     *
     * @param $rateJson
     * @param $request
     * @return void
     */
    public static function getTierBasedCommissionOptions($rateJson, $request)
    {
        $rateJson['tier_based_options']['based_on'] = $request->get('commission_type_options.tier_based_options.based_on');
        $based_on = $rateJson['tier_based_options']['based_on'];
        $type = $request->get('commission_type_options.tier_based_options.type');
        $rateJson['tier_based_options']['type'] = $type;
        $ranges = [];
        $tiers = $request->get('commission_type_options.tier_based_options.ranges');

        foreach ($tiers as $index => $tier) {
            $ranges[$index]['type'] = $type;

            if ($based_on == 'total_sales_amount') {
                $ranges[$index]['currency'] = $tier['currency'];
            }

            $ranges[$index]['condition'] = $tier['condition'];
            $ranges[$index]['value'] = $tier['value'];
        }

        $rateJson['tier_based_options']['ranges'] = $ranges;

        return $rateJson;
    }

    public static function getTierBasedAmountDetail($data, \WC_Order $wooOrder, $relayWpOrder, $commissionTier)
    {
        $matchedRange = static::getMatchedRange($wooOrder, $relayWpOrder, $commissionTier);

        if (empty($matchedRange)) {

            return [
                'commission_amount' => 0,
                'commission_display' => false
            ];
        }

        if ($matchedRange['type'] == 'percentage' && (int)($matchedRange['value']) > 0) {
            $totalOrderAmount = WC::getTotalPrice($wooOrder);

            $calculatedCommissionAmount = ($totalOrderAmount * ($matchedRange['value'] / 100));
        } else {
            $calculatedCommissionAmount = $matchedRange['value'];
        }

        $data = [
            'commission_amount' => $calculatedCommissionAmount,
        ];

        return apply_filters('rwpa_get_tier_based_commission_data', $data);
    }

    public static function getMatchedRange(\WC_Order $wooOrder, $relayWpOrder, $commissionTier)
    {
        $options = Functions::jsonDecode($commissionTier->rate_json);

        $ranges = $options['tier_based_options']['ranges'];

        if (empty($options)) {
            return false;
        }


        $based_on = $options['tier_based_options']['based_on'];

        $affiliateId = $wooOrder->get_meta(Affiliate::ORDER_AFFILIATE_FOR);

        $currency = $wooOrder->get_currency();

        if ($based_on == 'number_of_referrals') {
            $count = Customer::query()
                ->select('count(*) as customer_count')
                ->where("affiliate_id = $affiliateId")
                ->where("created_at <= %s", [$relayWpOrder->created_at])
                ->first();

            $value = $count->customer_count;
        } else if ($based_on == 'number_of_sales_count') {
            $count = Order::query()
                ->select('count(*) as sales_count')
                ->where("affiliate_id = $affiliateId")
                ->where("created_at <= %s", [$relayWpOrder->created_at])
                ->first();
            $value = $count->sales_count;
        } else if ($based_on == 'total_sales_amount') {
            $count = Order::query()
                ->select('sum(total_amount) as sales_amount')
                ->where("affiliate_id = $affiliateId")
                ->where("currency = %s", [$currency])
                ->where("created_at <= %s", [$relayWpOrder->created_at])
                ->first();

            $value = $count->sales_amount;
        }

        if ($value < 0) return false;

        if ($based_on == 'total_sales_amount') {
            $ranges = static::getSelectedCurrencyRanges($ranges, $currency);
        }

        //TODO: Handle currency for total sales amount type

        $range =  static::getMatchedRow($ranges, $value);

        return $range;
    }

    public static function getMatchedRow($ranges, $target)
    {
        $min_distances = [];
        foreach ($ranges as $range) {
            $min_distances[]  = $target - $range['condition'];
        }

        $minNonNegativeValue = null;
        $minNonNegativeIndex = null;


        foreach ($min_distances as $index => $value) {

            if ($value >= 0) {
                if ($minNonNegativeValue == null || $value < $minNonNegativeValue) {
                    $minNonNegativeValue = $value;
                    $minNonNegativeIndex = $index;
                }
            }
        }

        //TODO: === check is important
        if ($minNonNegativeIndex === null) return false;

        return $ranges[$minNonNegativeIndex];
    }

    public static function getSelectedCurrencyRanges($ranges, $currency)
    {
        $new_ranges = [];
        foreach ($ranges as $range) {
            if (isset($range['currency']['value']) && strtolower($range['currency']['value']) == strtolower($currency)) {
                $new_ranges[] = $range;
            }
        }

        return $new_ranges;
    }
}


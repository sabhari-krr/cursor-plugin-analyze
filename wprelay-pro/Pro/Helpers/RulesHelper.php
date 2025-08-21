<?php

namespace RelayWp\Affiliate\Pro\Helpers;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\PluginHelper;
use RelayWp\Affiliate\App\Services\Settings;
use RelayWp\Affiliate\Core\Models\Order;
use RelayWp\Affiliate\Pro\Models\Rules;
use RelayWp\Affiliate\Pro\Models\RulesCommissionDetail;
use RelayWp\Affiliate\Pro\Models\RulesMeta;

class RulesHelper
{

    /**
     * Retrieving Matched Bonus Ids:
     * @param $data array
     * @param \WC_Order $wooOrder
     * @param $relayWpOrder
     * @param $program
     * @param $affiliate
     */
    public static function calculateCommissions($data, \WC_Order $wooOrder, $relayWpOrder, $commissionTier)
    {
        try {
            $bonus_commissions = [];

            $program_id = $relayWpOrder->program_id;
            $affiliate_id = $relayWpOrder->affiliate_id;

            $available_rules = static::getAvailableRules($program_id, $affiliate_id);

            if (empty($available_rules)) {
                return [
                    'commission_amount' => 0,
                    'commission_display' => true,
                    'keep_entry' => true
                ];
            }

            foreach ($available_rules as $rule) {
                [$available, $amount, $additional_data] = static::checkCommissionAvailable($rule, $wooOrder);
                if (!$available || !apply_filters('rwpa_wprelay_rule_commission_calculate_for_order', true, $wooOrder, $rule)) continue;

                $bonus_commissions[] = [
                    'rule_id' => $rule->rule_id,
                    'commission_amount' => $amount,
                    'woo_order_id' => $wooOrder->get_id(),
                    'order_id' => $relayWpOrder->id,
                    'additional_data' => $additional_data
                ];
            }

            if (empty($bonus_commissions)) {
                return [
                    'commission_amount' => 0,
                    'commission_display' => true,
                    'keep_entry' => true
                ];
            }

            $total_bonus_amount = 0;

            $filter_type = apply_filters('rwpa_wprelay_rule_commission_get_setting', 'all_commission_amount');

            if (count($bonus_commissions) > 1 && $filter_type != 'all_commission_amount') {
                switch ($filter_type) {
                    case "highest_commission_amount":
                        $bonus_commissions = static::getSingleCommissionBasedOnSettings($bonus_commissions, 'highest_bonus_amount');
                        break;
                    case "least_commission_amount":
                        $bonus_commissions = static::getSingleCommissionBasedOnSettings($bonus_commissions, 'least_bonus_amount');
                        break;
                }
            }

            if (empty($bonus_commissions)) {
                return [
                    'commission_amount' => 0,
                    'commission_display' => true,
                    'keep_entry' => true
                ];
            }

            foreach ($bonus_commissions as $bonus_commission) {
                RulesCommissionDetail::query()
                    ->create([
                        'rule_id' => $bonus_commission['rule_id'],
                        'order_id' => $relayWpOrder->id,
                        'woo_order_id' => $wooOrder->get_id(),
                        'commission_amount' => $bonus_commission['commission_amount'],
                        'additional_data' => wp_json_encode($bonus_commission['additional_data'] ?? [])
                    ]);

                $total_bonus_amount += $bonus_commission['commission_amount'];
            }

            $updated_total_bonus_amount = apply_filters('rwpa_wprelay_get_bonus_amount', $total_bonus_amount, $wooOrder);

            if (!is_numeric($updated_total_bonus_amount)) {
                $updated_total_bonus_amount = 0;
            }

            return [
                'commission_amount' => $updated_total_bonus_amount,
            ];
        } catch (\Exception $exception) {
            PluginHelper::logError('Bonus Calculation Error', [__CLASS__, __FUNCTION__], $exception);
        }
    }

    public static function getSingleCommissionBasedOnSettings($bonus_commissions, $type)
    {
        $filterKey = 'bonus_amount';

        // Initialize the current commission as null
        $current = null;

        if ($type == 'least_commission_amount') {
            foreach ($bonus_commissions as $commission) {
                if ($current === null || $commission[$filterKey] <= $current[$filterKey]) {
                    $current = $commission;
                }
            }
        } else if ($type == 'highest_commission_amount') {
            foreach ($bonus_commissions as $commission) {
                if ($current === null || $commission[$filterKey] >= $current[$filterKey]) {
                    $current = $commission;
                }
            }
        }

        return empty($current) ? [] : [$current];
    }


    public static function getBonusCommission($amount, $type, $value)
    {
        if ($type == 'fixed') {
            return $value > 0 ? $value : 0;
        } else if ($type == 'percentage') {
            return ($amount * ($value / 100));
        }

        return 0;
    }

    /**
     * STEPS are explained in @param $relayWpOrder
     * @param $all_program_bonus_ids
     * @param $specific_program_bonus_ids
     * @param $all_affiliate_bonus_ids
     * @param $specific_affiliate_bonus_ids
     * @return array
     * @see static::calculateBonuses()
     */

    public static function checkCommissionAvailable($rule, \WC_Order $wooOrder)
    {
        $order_line_items = $wooOrder->get_items('line_item');
        $commission_data = $rule->commission_data;

        $commission_data = Functions::jsonDecode($commission_data);

        $product_ids = $commission_data['product_ids'] ?? [];
        $category_ids = $commission_data['category_ids'] ?? [];
        $based_on = $commission_data['based_on'];

        $product_amount = 0;
        $excludeTaxes = (bool)Settings::get('general_settings.commission_settings.exclude_taxes', false);

        $additional_data = [];

        $commission_available = false;

        foreach ($order_line_items as $item_id => $item) {
            $available = false;
            //Get the product ID
            $product_id = $item->get_product_id();

            $product = $item->get_product();

            if ($based_on == 'all_products') {
                $available = true;
            }

            if ($based_on == 'product_in_list') {
                //                || (!empty($variation_id) && in_array($variation_id, $product_ids))
                if (in_array($product_id, $product_ids)) {
                    $available = true;
                } else if ($product instanceof \WC_Product_Variation) {
                    $variation_id = $item->get_variation_id();
                    if (in_array($variation_id, $product_ids)) {
                        $available = true;
                    }
                }
            } //check the item product id is present in bonus products

            if ($based_on == 'product_not_in_list') {
                //here the order is important
                if ($product instanceof \WC_Product_Variation) {
                    $parent_id = $product->get_parent_id('edit');
                    $variation_id = $item->get_variation_id();
                    if (!in_array($parent_id, $product_ids) && !in_array($variation_id, $product_ids)) {
                        $available = true;
                    }
                } else if (!in_array($product_id, $product_ids)) {
                    $available = true;
                }
            }

            if ($based_on == 'category_in_list') {
                if ($product instanceof \WC_Product_Variation) {
                    $parent_id = $product->get_parent_id('edit');
                    $parent_product = wc_get_product($parent_id);
                    $product_category_ids = $parent_product->get_category_ids();
                } else {
                    $product_category_ids = $product->get_category_ids();
                }

                if (!empty(array_intersect($product_category_ids, $category_ids))) {
                    $available = true;
                }
            }

            if ($based_on == 'category_not_in_list') {
                if ($product instanceof \WC_Product_Variation) {
                    $parent_id = $product->get_parent_id('edit');
                    $parent_product = wc_get_product($parent_id);
                    $product_category_ids = $parent_product->get_category_ids();
                } else {
                    $product_category_ids = $product->get_category_ids();
                }

                if (empty(array_intersect($product_category_ids, $category_ids))) {
                    $available = true;
                }
            }

            if ($available) {
                $commission_available = true;
                $additional_data[$rule->rule_id]['exclude_taxes'] = $excludeTaxes;
                $additional_data[$rule->rule_id]['bonus_data'] = $commission_data;
                $amount = static::getItemAmountForBonusCalculation($item, $excludeTaxes);
                $product_amount += ($amount > 0) ? static::getBonusCommission($amount, $commission_data['type'], $commission_data['value']) : 0;
            }
        }

        return [$commission_available, $product_amount, $additional_data];
    }

    public static function getItemAmountForBonusCalculation(\WC_Order_Item $item, $excludeTaxes)
    {
        $lineItemTaxAmount = $item->get_total_tax();

        //        if customer want to issue commisison amount for each product need to update logi by using filter

        $total_amount = $item->get_total() + $lineItemTaxAmount;

        if ($excludeTaxes) {
            // Get the tax data
            $total_amount -= $lineItemTaxAmount;
        }

        return $total_amount;
    }

    public static function getAvailableRules($program_id, $affiliate_id)
    {
        $rulesTable = Rules::getTableName();
        $rulesMetaTable = RulesMeta::getTableName();

        $current_utc_time = Functions::currentUTCTime();

        //STEP = 1
        $rules = Rules::query()->select("{$rulesTable}.id as rule_id, commission_data, affiliates_type, GROUP_CONCAT({$rulesMetaTable}.model_id SEPARATOR ',') as affiliate_ids")
            ->leftJoin($rulesMetaTable, "{$rulesMetaTable}.rule_id = {$rulesTable}.id AND {$rulesMetaTable}.type = 'affiliate'")
            ->where("program_id = %d", [$program_id])
            ->where("status = %s", ['active'])
            ->where("(start_date <= %s OR start_date is null)", [$current_utc_time])
            ->where("(end_date > %s OR end_date is null)", [$current_utc_time])
            ->where("deleted_at is null")
            ->groupBy("{$rulesTable}.id");


        $rules = $rules->get();

        $available_rules = [];

        foreach ($rules as $rule) {
            if ($rule->affiliates_type == 'all') {
                $available_rules[] = $rule;
            } else {
                $ids = $rule->affiliate_ids;
                if (empty($ids)) continue;

                $current_affiliate_ids = explode(',', $ids);
                if (in_array($affiliate_id, $current_affiliate_ids)) {
                    $available_rules[] = $rule;
                }
            }
        }
        return $available_rules;
    }


    public static function calculateBonusCommission($data, \WC_Order $wooOrder, $relayWpOrder, $commissionTier)
    {
        //@see static::calculateCommissions

        $data = apply_filters("rwpa_get_commission_details_for_rule_based_type", [], $wooOrder, $relayWpOrder, $commissionTier);
        return apply_filters('rwpa_commission_details', $data, $wooOrder);
    }

    public static function isProductIsValidForAffiliateLink($status, $product_id, $program_id, $affiliate_id)
    {
        if (empty($program_id) || empty($affiliate_id)) return [];

        $rules = static::getAvailableRules($program_id, $affiliate_id);

        $product = wc_get_product($product_id);

        $available = false;

        foreach ($rules as $rule) {
            //Get the product ID
            $commission_data = $rule->commission_data;

            $commission_data = Functions::jsonDecode($commission_data);

            $product_ids = $commission_data['product_ids'] ?? [];
            $category_ids = $commission_data['category_ids'] ?? [];
            $based_on = $commission_data['based_on'];


            if ($based_on == 'all_products') {
                $available = true;
            } else if ($based_on == 'product_in_list') {
                //                || (!empty($variation_id) && in_array($variation_id, $product_ids))
                if (in_array($product_id, $product_ids)) {
                    $available = true;
                } else if ($product instanceof \WC_Product_Variation) {
                    $parent_id = $product->get_parent_id('edit');
                    if (in_array($parent_id, $product_ids)) {
                        $available = true;
                    }
                }
            } else if ($based_on == 'product_not_in_list') {
                //here the order is important
                if ($product instanceof \WC_Product_Variation) {
                    $parent_id = $product->get_parent_id('edit');
                    if (!in_array($parent_id, $product_ids)) {
                        $available = true;
                    }
                } else if (!in_array($product_id, $product_ids)) {
                    $available = true;
                }
            } else if ($based_on == 'category_in_list') {
                if ($product instanceof \WC_Product_Variation) {
                    $parent_id = $product->get_parent_id('edit');
                    $parent_product = wc_get_product($parent_id);
                    $product_category_ids = $parent_product->get_category_ids();
                } else {
                    $product_category_ids = $product->get_category_ids();
                }

                if (!empty(array_intersect($product_category_ids, $category_ids))) {
                    $available = true;
                }
            }

            if ($based_on == 'category_not_in_list') {
                if ($product instanceof \WC_Product_Variation) {
                    $parent_id = $product->get_parent_id('edit');
                    $parent_product = wc_get_product($parent_id);
                    $product_category_ids = $parent_product->get_category_ids();
                } else {
                    $product_category_ids = $product->get_category_ids();
                }

                if (empty(array_intersect($product_category_ids, $category_ids))) {
                    $available = true;
                }
            }

            if ($available) {
                return true;
            }
        }

        return false;
    }
}

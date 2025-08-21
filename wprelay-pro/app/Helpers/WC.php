<?php

namespace RelayWp\Affiliate\App\Helpers;

defined('ABSPATH') or exit;

use RelayWp\Affiliate\App\Services\Database;
use RelayWp\Affiliate\App\Services\Settings;
use WC_Countries;

class WC
{
    public static function saveCoupon($couponData, $coupon = null)
    {

        if (!function_exists('WC')) {
            return false;
        }

        // Check if WooCommerce is activated
        if (!class_exists('WC_Coupon')) {
            return false;
        }

        // Create a coupon object
        $coupon = empty($coupon) ? new \WC_Coupon() : $coupon;

        // Set coupon code
        $coupon_code = $couponData['code'];

        $coupon->set_code($coupon_code);

        $coupon->set_status($couponData['status']);

        // Set coupon discount type and amount
        $coupon->set_discount_type($couponData['discount_type']);
        $coupon->set_amount($couponData['discount_value']);

        // Set other coupon details based on $couponData
        $coupon->set_individual_use($couponData['individual_use']);

        $usage_limit_per_user = $couponData['usage_limit_per_user'] ?? null;

        $coupon->set_usage_limit_per_user($couponData['usage_limit_per_user']);

        $coupon->set_usage_limit($couponData['usage_limit_per_coupon']);
        $coupon->set_minimum_amount($couponData['minimum_amount']);
        $coupon->set_maximum_amount($couponData['maximum_amount']);

        $coupon->set_product_ids($couponData['product_ids']);
        $coupon->set_excluded_product_ids($couponData['excluded_product_ids']);
        $coupon->set_product_categories($couponData['category_ids']);
        $coupon->set_excluded_product_categories($couponData['excluded_category_ids']);

        // Set free shipping status
        $coupon->set_free_shipping($couponData['free_shipping']);
        $coupon->set_exclude_sale_items($couponData['exclude_sale_items']);

        // Set expiry date if provided
        if (!empty($couponData['expiry_date'])) {
            $coupon->set_date_expires(strtotime($couponData['expiry_date']));
        }

        foreach ($couponData['meta_data'] as $key => $value) {
            $coupon->add_meta_data($key, $value);
        }

        // Save the coupon
        $coupon_id = $coupon->save();

        return $coupon_id;
    }

    public static function getProductNameWithID($product_id, $prefix = '#')
    {
        return $prefix . $product_id . ' ' . html_entity_decode(get_the_title($product_id));
    }

    public static function getTerms(array $args = []): array
    {
        $args = array_merge([
            'search' => '',
            'taxonomy' => '',
            'hide_empty' => false,
        ], $args);

        $terms = get_terms($args);

        return is_array($terms) ? $terms : [];
    }

    public static function getCategoryParent($category_id): string
    {
        if (empty($category_id) || $category_id < 0) return '';

        $category = WC::getCategory($category_id);
        if (is_object($category)) return '';

        $label = !empty($category->parent) ? self::getCategoryParent($category->parent) . ' -> ' : '';
        $label .= !empty($category->term_id) ? $category->name : '';
        return $label;
    }


    public static function getCategory($category_id)
    {
        return self::getTerm($category_id, 'product_cat');
    }


    public static function getTerm($term_id, string $taxonomy = '')
    {
        if (!is_numeric($term_id) || $term_id <= 0) return false;
        $term = get_term($term_id, $taxonomy);

        return is_object($term) ? $term : false;
    }

    public static function getIdsFromLabelsArray($items)
    {
        if (!is_array($items)) return [];

        $ids = [];
        foreach ($items as $item) {
            if (isset($item['value'])) {
                $ids[] = $item['value'];
            }
        }
        return $ids;
    }

    public static function getCountryWithLabel($countryCode)
    {
        if (!$countryCode) return null;

        $wc_countries = new WC_Countries();

        // Define the country code you want to retrieve details for
        $country_code = $countryCode; // Replace with the desired country code

        $countries = $wc_countries->get_countries();

        // Check if the country code exists in the list
        if (isset($countries[$country_code])) {

            return [
                'value' => $country_code,
                'label' => $countries[$country_code]
            ];
            // Add more details as needed
        } else {
            return [];
        }
    }

    public static function getStateWithLabel($countryCode, $stateCode)
    {
        $states = WC::getStates($countryCode);

        if (isset($states[$stateCode])) {
            return [
                'value' => $stateCode,
                'label' => $states[$stateCode]
            ];
            // Add more details as needed
        } else {
            return null;
        }
    }

    public static function getWooCommerceCurrencySymbol($currencyCode = '')
    {
        $currency = get_woocommerce_currency_symbol($currencyCode);
        return html_entity_decode($currency);
    }

    static function getSession($key, $default = NULL)
    {
        if (static::isWCSessionLoaded() && Util::isMethodExists(WC()->session, 'get')) {
            return WC()->session->get($key, $default);
        }
        return $default;
    }

    /**
     * set the session value by key
     * @param $key
     * @param $value mixed
     */
    static function setSession($key, $value)
    {
        if (static::isWCSessionLoaded() && Util::isMethodExists(WC()->session, 'set')) {
            WC()->session->set($key, $value);
        }
    }

    static function isWCSessionLoaded()
    {
        return function_exists('WC') && isset(WC()->session) && WC()->session != null;
    }

    public static function getAppliedCouponsforOrder($order_id)
    {
        if (!is_object($order_id) && empty($order_id)) {
            $order = wc_get_order($order_id);
        } else {
            $order = $order_id;
        }

        // Get applied coupons from the order
        $applied_coupons = $order->get_coupon_codes();

        return $applied_coupons;
    }

    public static function getTotalPrice(\WC_Order $order)
    {
        $excludeShipping = (bool)Settings::get('general_settings.commission_settings.exclude_shipping', false);
        $excludeTaxes = (bool)Settings::get('general_settings.commission_settings.exclude_taxes', false);

        //with tax and with shipping
        $orderTotal = $order->get_total();

        if ($excludeTaxes) {
            $orderTotal -= $order->get_total_tax();
        }

        if ($excludeShipping) {
            $orderTotal -= $order->get_shipping_total();
            $orderTotal -= $order->get_shipping_tax();
        }

        return apply_filters('rwpa_get_total_price_of_the_order', $orderTotal);
    }

    public static function removeCoupon(string $code): bool
    {
        return function_exists('WC') && isset(WC()->cart)
            && Util::isMethodExists(WC()->cart, 'remove_coupon') && WC()->cart->remove_coupon($code);
    }

    public static function isCouponExists(string $coupon_code)
    {
        global $wpdb;
        $coupon = Database::table($wpdb->posts)
            ->select("ID, post_status")
            ->where("post_type = %s AND post_title = %s", ['shop_coupon', $coupon_code])
            ->first();

        // If coupon is found, check if it's in trash or not
        if ($coupon) {
            return $coupon;
        }

        return false; // Coupon does not exist    }    }
    }

    public static function getAffilateEndPoint()
    {
        $endpoint_name = apply_filters('rwpa_affiliate_get_endpoint_name', 'relay-affiliate-marketing');

        return wc_get_account_endpoint_url($endpoint_name);
    }

    public static function getStoreName()
    {
        return get_bloginfo('name');
    }

    public static function getAdminDashboard()
    {
        $pluginSlug = RWPA_PLUGIN_SLUG;

        return admin_url("admin.php?page={$pluginSlug}#");
    }

    public static function getStates($countryCode)
    {
        if (empty($countryCode)) return [];

        $woo_countries = new WC_Countries();

        $states = $woo_countries->get_states($countryCode);

        if (empty($states)) return [];

        return $states;
    }

    public static function getOrderEditUrl($order_id)
    {
        return null;
        get_edit_post_link($order_id, '');
    }

    public static function getWcCurrencySymbol($code = 'USD')
    {
        if (function_exists('get_woocommerce_currency_symbol')) {
            return html_entity_decode(get_woocommerce_currency_symbol($code));
        }

        return '$';
    }

    public static function getCurrencyList()
    {
        $currencies = get_woocommerce_currencies();

        return $currencies;
    }

    public static function isHPOSEnabled()
    {
        if (! class_exists('\Automattic\WooCommerce\Utilities\OrderUtil')) {
            return false;
        }

        if (\Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
            return true;
        }

        return false;
    }

    public static function isCouponAvailable($coupon_code)
    {
        if (empty($coupon_code)) return false;

        $coupon = new \WC_Coupon($coupon_code);

        $usage_count = $coupon->get_usage_count();
        $usage_limit = $coupon->get_usage_limit();

        if ($usage_limit && $usage_count < $usage_limit) {
            return true;
        }

        return false;
    }
}

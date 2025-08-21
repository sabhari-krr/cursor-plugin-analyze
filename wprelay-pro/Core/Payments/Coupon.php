<?php

namespace RelayWp\Affiliate\Core\Payments;

defined('ABSPATH') or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\Core\Models\Affiliate;
use RelayWp\Affiliate\Core\Models\CouponPayout;
use RelayWp\Affiliate\Core\Models\Payout;
use RelayWp\Affiliate\App\Helpers\WordpressHelper;
use RelayWp\Affiliate\Core\Models\Member;

class Coupon extends RWPPayment
{
    protected $payout = null;

    public static function addCouponPayment($paymentMethods)
    {
        $paymentMethods['coupon'] = new self();

        return $paymentMethods;
    }

    public function getPaymentSource()
    {
        $name = RWPA_PLUGIN_SLUG;
        return [
            'name' => __('Coupon', 'relay-affiliate-marketing'),
            'value' => 'coupon',
            'label' => __('Coupon Payment', 'relay-affiliate-marketing'),
            'description' => __('Process Payouts for your affiliates through WooCommerce coupons', 'relay-affiliate-marketing'),
            'note' => __('This payment method converts the commission amount into a one-time-use WooCommerce coupon, which can be redeemed for future purchases.', 'relay-affiliate-marketing'),
            'disabled' => false,
            'target_url' => admin_url("admin.php?page={$name}#/settings/coupon-settings"),
        ];
    }

    /**
     * @param $payout
     * @return void
     */
    public function process($payout_ids)
    {
        if (\ActionScheduler::is_initialized()) {
            as_schedule_single_action(strtotime("now"), 'rwpa_process_coupon_payouts', [$payout_ids]);
        } else {
            error_log('ActionScheduler not initialized so Unable to process Payouts Via Loyalty Points');
        }
    }


    public static function isCouponPaymentAvailable($status, $currency_code)
    {
        return get_woocommerce_currency() === $currency_code;
    }

    public static function sendPayments($payout_ids)
    {
        $ids = implode("','", $payout_ids);

        $memberTable = Member::getTableName();
        $affiliateTable = Affiliate::getTableName();
        $payoutTable = Payout::getTableName();


        $payouts = Payout::query()
            ->select("{$payoutTable}.*, {$memberTable}.email as affiliate_email")
            ->leftJoin($affiliateTable, "$affiliateTable.id = $payoutTable.affiliate_id")
            ->leftJoin($memberTable, "$memberTable.id = $affiliateTable.member_id")
            ->where("{$payoutTable}.id in ('" . $ids . "')")
            ->get();

        $data = [];

        foreach ($payouts as $payout) {
            if (in_array($payout->id, $payout_ids)) {
                $data[] = [
                    'affiliate_email' => $payout->affiliate_email,
                    'commission_amount' => $payout->amount,
                    'currency' => $payout->currency,
                    'affiliate_id' => $payout->affiliate_id,
                    'affiliate_payout_id' => $payout->id,
                ];
            }
        }

        foreach ($data as $item) {
            $coupon_code = WordpressHelper::generateRandomString(10);
            $prefix_string = apply_filters('rwpa_coupon_payout_prefix', 'RELAY_');
            $coupon_code = "{$prefix_string}{$coupon_code}";

            $coupon_data = CouponPayout::getCouponDefaults();

            $options = get_option(CouponPayout::OPTION_KEY, "[]");
            $options = Functions::jsonDecode($options);
            $enable_minimum_payout_threshold = $options['enable_minimum_payout_threshold'] ?? false;
            $minimum_thersold_payout_amount = $options['minimum_thersold_payout_amount'] ?? 0;

            $coupon_data = array_merge($coupon_data, [
                'code' => $coupon_code,
                'discount_value' => $item['commission_amount'],
                'discount_type' => 'fixed_cart',
                'date_expires' => null,
                'minimum_amount' => $enable_minimum_payout_threshold ? $minimum_thersold_payout_amount : 0,
            ]);

            $coupon_id = CouponPayout::createCouponForPayout($coupon_data);

            $is_coupon_successfully_created = false;

            if (!empty($coupon_id)) {
                CouponPayout::query()->create([
                    'payout_id' => $item['affiliate_payout_id'],
                    'coupon_id' => $coupon_id,
                    'coupon_code' => $coupon_code,
                    'extra_data' => wp_json_encode($coupon_data)
                ]);

                $lastInsertedId = CouponPayout::query()->lastInsertedId();

                if (!empty($lastInsertedId)) {
                    $is_coupon_successfully_created = true;
                }
            }

            if ($is_coupon_successfully_created) {
                $message = __('Payout Coupon Successfully Created', 'relay-affiliate-marketing');

                do_action('rwpa_payment_mark_as_succeeded', $item['affiliate_payout_id'], ['message' => $message]);
            } else {
                $message = __('Unable to create payout coupon. coupon creation failed', 'relay-affiliate-marketing');
                do_action('rwpa_payment_mark_as_failed', $item['affiliate_payout_id'], ['message' => $message]);
            }
        }
    }
}

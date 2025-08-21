<?php

namespace RelayWp\Affiliate\Core\Models;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\PluginHelper;
use RelayWp\Affiliate\App\Helpers\WC;
use RelayWp\Affiliate\App\Helpers\WordpressHelper;
use Cartrabbit\Request\Request;
use RelayWp\Affiliate\App\Model;
use RelayWp\Affiliate\App\Services\Settings;
use WC_Customer;
use WpOrg\Requests\Exception;

class Affiliate extends Model
{
    protected static $table = 'affiliates';

    public const AFFILIATE_META_KEY_FOR_ORDER = '_relay_wp_affiliate_code';
    public const ORDER_FROM_META_KEY = '_relay_wp_order_from';
    public const  ORDER_AFFILIATE_FOR = '_relay_wp_affiliate_id';
    public const  ORDER_AFFILIATE_SESSION_EMIL = '_relay_wp_order_session_email';
    public const  ORDER_RECURRING_ID = '_relay_wp_recurring_order_id';
    public const ORDER_PLACED_EMAIL_SEND = '_relay_wp_order_email_send';


    public function createTable()
    {
        $charset = static::getCharSetCollate();

        $table = static::getTableName();

        $memberTable = Member::getTableName();
        $programTable = Program::getTableName();

        return "CREATE TABLE {$table} (
                id BIGINT UNSIGNED AUTO_INCREMENT,
                member_id BIGINT UNSIGNED,
                program_id BIGINT UNSIGNED NULL,
                payment_email VARCHAR(255) NULL,
                status VARCHAR(255) NOT NULL,
                phone_number VARCHAR(255),
                referral_code VARCHAR(255) NULL,
                social_links JSON NULL,
                shipping_address JSON NULL,
                tags JSON NULL,
                extra_data JSON NULL,
                meta_data JSON NULL,
                is_email_verified int default 0,
                create_wc_account int default 0,
                wp_customer_id BIGINT UNSIGNED NULL,
                is_wc_account_created int default 0,
                date_registered TIMESTAMP NULL DEFAULT current_timestamp(),
                created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
                updated_at TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                deleted_at TIMESTAMP NULL,
                PRIMARY KEY (id)
                ) {$charset};";
    }

    public static function getTagsFromRequest(Request $request, $json = true)
    {
        $tags = $request->get('tags');
        return wp_json_encode($tags);
    }

    public static function getReferralCodeURL($affiliate, $key = 'referral_code')
    {
        $code = $affiliate->{$key};

        $shopURL = self::getHomeURL();

        $urlVariable = Settings::getAffiliateReferralURLVariable();

        $shopURL = apply_filters('rwpa_get_referral_code_url', $shopURL, $code, $key);

        $query = wp_parse_url($shopURL, PHP_URL_QUERY);

        if (empty($query)) {
            return $shopURL . "?{$urlVariable}={$code}";
        } else {
            return $shopURL . "&{$urlVariable}={$code}";
        }
    }

    public static function getHomeURL()
    {
        return get_home_url();
    }

    public static function getReferralURLWithoutCode()
    {
        $shopURL = self::getHomeURL();

        $urlVariable = Settings::getAffiliateReferralURLVariable();

        return $shopURL . "?{$urlVariable}=";
    }

    public static function createCoupon($affiliate, $program)
    {
        $customerDiscount = CustomerDiscount::query()->where("program_id = {$program->id}")->first();

        $couponData = static::getWooCommerceCouponData($affiliate, $program, $customerDiscount);

        $couponId = WC::saveCoupon($couponData);

        if (empty($couponId)) return false;

        //        $coupon = wc_get_coupon_code_by_id($couponId);
        $coupon = new \WC_Coupon($affiliate->referral_code);

        $data = AffiliateCoupon::getCouponData($coupon);

        AffiliateCoupon::query()->create(array_merge($data, [
            'affiliate_id' => $affiliate->id,
            'created_at' => Functions::currentUTCTime(),
            'updated_at' => Functions::currentUTCTime()
        ]));
    }

    public static function updateCoupon($affiliate, $program, $customerDiscount)
    {
        $affiliateCoupon = AffiliateCoupon::query()
            ->where('affiliate_id = %s', [$affiliate->id])
            ->where('is_primary = %d', [1])
            ->where('deleted_at IS NULL')
            ->first();

        if (empty($affiliateCoupon)) return false;

        $couponData = static::getWooCommerceCouponData($affiliate, $program, $customerDiscount);
        $coupon_code = wc_get_coupon_code_by_id($affiliateCoupon->woo_coupon_id);

        $coupon = new \WC_Coupon($coupon_code);

        $couponId = WC::saveCoupon($couponData, $coupon);


        $data = AffiliateCoupon::getCouponData($coupon);

        AffiliateCoupon::query()->update(array_merge($data, [
            'affiliate_id' => $affiliate->id,
            'updated_at' => Functions::currentUTCTime()
        ]), ['id' => $affiliateCoupon->id]);
    }

    public static function setShippingAddress($data)
    {
        if (!is_array($data)) return null;

        return Functions::arrayToJson([
            'address' => $data['address'] ?? null,
            'city' => $data['city'],
            'state' => isset($data['state']['value']) ? $data['state']['value'] : null,
            'zip_code' => $data['zip_code'],
            'country' => isset($data['country']['value']) ? $data['country']['value'] : null,
        ]);
    }

    public static function getShippingAddressDetails($shippingAddress)
    {
        $data = Functions::jsonDecode($shippingAddress ?? null);
        if (!is_array($data)) return [
            'address' => '',
            'city' => '',
            'zip_code' => '',
            'state' => null,
            'country' => null,
        ];

        return [
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => WC::getStateWithLabel($data['country'] ?? null, $data['state'] ?? null),
            'zip_code' => $data['zip_code'] ?? null,
            'country' => WC::getCountryWithLabel($data['country'] ?? null),
        ];
    }

    public static function setTags($tags)
    {
        if (!is_array($tags) || empty($tags)) return null;

        return Functions::arrayToJson($tags);
    }

    public static function setSocialLinks($links)
    {
        if (!is_array($links) || empty($links)) return null;

        return Functions::arrayToJson($links);
    }

    public static function getSocialLinks($links)
    {
        $links = Functions::jsonDecode($links);

        if (empty($links)) {
            $links = [];
        }

        $links['facebook_url'] = $links['facebook_url'] ?? null;
        $links['youtube_url'] = $links['youtube_url'] ?? null;
        $links['instagram_url'] = $links['instagram_url'] ?? null;
        $links['twitter_url'] = $links['twitter_url'] ?? null;
        $links['linkedin_url'] = $links['linkedin_url'] ?? null;
        $links['website_url'] = $links['website_url'] ?? null;
        $links['tiktok_url'] = $links['tiktok_url'] ?? null;

        return $links;
    }

    public static function getWooCommerceCouponData($affiliate, $program, $customerDiscount)
    {
        return [
            'code' => $affiliate->referral_code,
            'status' => 'publish',
            'discount_type' => CustomerDiscount::getCouponDiscountType($customerDiscount->discount_type),
            'discount_value' => $customerDiscount->coupon_amount,
            'product_ids' => Functions::jsonDecode($customerDiscount->product_ids),
            'excluded_product_ids' => Functions::jsonDecode($customerDiscount->exclude_product_ids),
            'category_ids' => Functions::jsonDecode($customerDiscount->category_ids),
            'excluded_category_ids' => Functions::jsonDecode($customerDiscount->exclude_category_ids),
            'expiry_date' => $customerDiscount->expiry_date,
            'apply_before_tax' => true,
            'free_shipping' => $customerDiscount->free_shipping,
            'individual_use' => $customerDiscount->individual_use,
            'usage_limit_per_user' => $customerDiscount->usage_limit_enabled ? $customerDiscount->usage_limit_per_user : null,
            'usage_limit_per_coupon' => null,
            'minimum_amount' => $customerDiscount->min_requirements_enabled ? $customerDiscount->minimum_amount : null,
            'maximum_amount' => $customerDiscount->min_requirements_enabled ? $customerDiscount->maximum_amount : null,
            'exclude_sale_items' => $customerDiscount->min_requirements_enabled ? $customerDiscount->exclude_sale_items : false,
            'meta_data' => [
                'is_rwp_coupon' => true,
                //                'rwp_minimum_quantity' => ($minRequirements['enabled'] && $minRequirements['type'] == 'no_of_items') ? $minRequirements['value'] : -1,
                'rwp_affiliate_id' => $affiliate->id,
                'rwp_program_id' => $program->id,
            ]
        ];
    }

    public static function createWCAccount($member, $affiliate)
    {
        try {

            $parts = explode("@", $member->email);

            $username = $parts[0];

            $user_data = array(
                'user_login' => $username . $affiliate->id,  // The user's username
                'user_pass' => WordpressHelper::generateRandomPassword(10), // The user's password
                'user_email' => $member->email,
                'role' => 'customer', // Assign the role of 'customer'
                'first_name' => $member->first_name,
                'last_name' => $member->last_name
            );

            if (email_exists($member->email)) {
                return false;
            }

            $user_id = wc_create_new_customer($user_data['user_email'], $user_data['user_login'], $user_data['user_pass'], $user_data);

            if (!is_wp_error($user_id)) {
                // Set the role of the user to 'customer'
                $customer = new \WC_Customer($user_id);
                $customer->set_role('customer');
                $customer->set_role('affiliate');
                return $user_id;
            } else {
                //code
            }

            return false;
        } catch (\Error $error) {
            return false;
        }
    }

    public static function isAffiliateApproved($status): bool
    {
        return $status == 'approved';
    }


    public static function autoApprove($affiliateId)
    {
        $affiliate = Affiliate::query()->findOrFail($affiliateId);
        $member = Member::query()->findOrFail($affiliate->member_id);
        $default_program_id = Settings::get('affiliate_settings.general.default_program_id') ?? null;

        if (!empty($affiliate->program_id)) {
            $program = Program::query()->find($affiliate->program_id);
        } else {
            if (!empty($default_program_id)) {
                $program = Program::query()->find($default_program_id);
            }
        }

        if (!empty($program)) {
            $auto_approve = (bool)$program->auto_approve;

            if ($auto_approve && is_null($affiliate->referral_code)) {

                $settingsOption = Settings::get('affiliate_settings.url_options.referral_code_value') ?? 'random';

                $coupon_code = static::getCouponCode($settingsOption, $member);

                $is_coupon_exists = WC::isCouponExists($coupon_code);

                if ($is_coupon_exists) {
                    $coupon_code = static::getCouponCode('random', $member);
                    $is_coupon_exists = WC::isCouponExists($coupon_code);
                }

                if (is_bool($is_coupon_exists) && !$is_coupon_exists) {

                    Affiliate::query()->update([
                        'program_id' => $program->id,
                        'referral_code' => $coupon_code,
                        'status' => 'approved'
                    ], ['id' => $affiliate->id]);
                    $affiliate = Affiliate::query()->findOrFail($affiliateId);
                    Affiliate::createCoupon($affiliate, $program);
                }

                $send_affiliate_approved_email = Settings::get('email_settings.affiliate_emails.affiliate_approved');

                $data = [
                    'first_name' => $member->first_name,
                    'last_name' => $member->last_name,
                    'email' => $member->email,
                    'referral_code' => $affiliate->referral_code,
                    'referral_link' => Affiliate::getReferralCodeURL($affiliate)
                ];

                if ($send_affiliate_approved_email) {
                    $commissionTier = CommissionTier::query()->where("program_id = %d", [$affiliate->program_id])->first();
                    $commission = CommissionTier::getCommissionTierInfo($commissionTier);
                    $data['commission_type'] = $commission['type_formatted'] ?? '';
                    $data['commission_value'] = $commission['commission_value'];
                    do_action('rwpa_send_affiliate_approved_email', $data);
                }
            } else {
                Affiliate::query()->update([
                    'program_id' => $program->id,
                ], ['id' => $affiliate->id]);
            }
        }
    }

    public static function addAffiliateQueryParam($affiliate, $url)
    {
        $urlVariable = Settings::getAffiliateReferralURLVariable();
        return add_query_arg($urlVariable, $affiliate->referral_code, $url);
    }

    public static function getAffiliateMetaData($meta_data)
    {
        $meta_data = Functions::jsonDecode($meta_data);

        if (empty($meta_data)) return null;

        return array_map(function ($field) {
            return [
                'label' => $field['label'],
                'value' => $field['value']
            ];
        }, $meta_data);
    }

    public static function getCouponCode($settingsOption, $member)
    {
        switch ($settingsOption) {
            case 'first_name':
                $value = $member->first_name;
                break;
            case 'last_name':
                $value = $member->last_name;
                break;
            default:
                $value = WordpressHelper::generateRandomString(12);
                break;
        }

        if (empty($value)) {
            $value = WordpressHelper::generateRandomString(12);
        }

        return $value;
    }

}

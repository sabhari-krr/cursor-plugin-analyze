<?php

namespace RelayWp\Affiliate\Core\Resources\Affiliate;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Collection;

class AffiliateCouponCollection extends Collection
{
    public function toArray($items, $affiliate)
    {
        $data = [];

        foreach ($items as $item) {
            $data[] = [
                'code' => $item->coupon,
                'is_primary' => $item->is_primary,
                'status' => $item->status,
                'amount' => $item->discount_value,
                'discount_type' => $item->discount_type,
                'free_shipping' => $item->free_shipping,
                'min_amount' => $item->minimum_amount,
                'max_amount' => $item->maximum_amount,
                'min_amount_formatted' => Functions::formatAmount($item->minimum_amount),
                'max_amount_formatted' => Functions::formatAmount($item->maximum_amount),
                'usage_limit' => $item->usage_limit_per_coupon,
                'usage_limit_per_user' => $item->usage_limit_per_user,
                'discount_amount' => $item->discount_amount,
                'woo_coupon_id' => $item->woo_coupon_id,
                'affiliate_id' => $affiliate->id,
                'deleted_at' => $item->deleted_at,
            ];
        }

        return [
            'coupons' => $data
        ];
    }
}


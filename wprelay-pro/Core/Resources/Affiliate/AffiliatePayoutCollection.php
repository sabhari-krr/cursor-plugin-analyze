<?php

namespace RelayWp\Affiliate\Core\Resources\Affiliate;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\WC;

use RelayWp\Affiliate\App\Collection;

class AffiliatePayoutCollection extends Collection
{
    public function toArray($items, $affiliate, $totalCount, $perPage, $currentPage)
    {
        $data = [];
        foreach ($items as $item) {
            $data[] = [
                'currency' => WC::getWooCommerceCurrencySymbol($item->currency),
                'amount' => $item->amount,
                'affiliate_note' => $item->affiliate_note,
                'coupon_code' => $item->coupon_code ?? null,
                'is_coupon_usage_available' => WC::isCouponAvailable($item->coupon_code),
                'status' => ucwords($item->status),
                'admin_note' => $item->admin_note,
                'payment_type' => ucwords($item->payment_source),
                'formatted_amount' => Functions::formatAmount($item->amount, $item->currency),
                'paid_at' => Functions::getWcTime($item->created_at),
                'deleted_at' => Functions::getWcTime($item->deleted_at),
                'revert_reason' => $item->revert_reason,
                'affiliate_id' => $affiliate->id
            ];
        }
        return [
            "total" => $totalCount,
            "per_page" => $perPage,
            "total_pages" => ceil($totalCount / $perPage),
            "current_page" => $currentPage,
            'payouts' => $data
        ];
    }
}

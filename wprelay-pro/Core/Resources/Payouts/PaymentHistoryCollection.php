<?php

namespace RelayWp\Affiliate\Core\Resources\Payouts;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\WC;
use RelayWp\Affiliate\Core\Models\Affiliate;
use RelayWp\Affiliate\App\Collection;

class PaymentHistoryCollection extends Collection
{
    public function toArray($items, $totalCount, $perPage, $currentPage)
    {
        $data = [];
        foreach ($items as $item) {
            $data[] = [
                'payout_id' => $item->payout_id,
                'payment_details' => [
                    'payment_source' => ucfirst($item->payment_source),
                ],
                'additional_details' => $item->additional_details,
                'coupon_code' => $item->coupon_code,
                'is_coupon_usage_available' => WC::isCouponAvailable($item->coupon_code),
                'affiliate' => [
                    'email' => $item->affiliate_email,
                    'affiliate_id' => $item->affiliate_id,
                    'name' => $item->affiliate_first_name . ' ' . $item->affiliate_last_name,
                ],
                'amount_paid' => $item->paid_amount,
                'currency' => WC::getWcCurrencySymbol($item->currency),
                'amount_paid_formatted' => Functions::formatAmount($item->paid_amount, $item->currency),
                'paid_at' => Functions::utcToWPTime($item->paid_at),
                'status' => $item->status,
                'deleted_at' => $item->deleted_at,
            ];
        }
        return [
            "total" => $totalCount,
            "per_page" => $perPage,
            "total_pages" => ceil($totalCount / $perPage),
            "current_page" => $currentPage,
            'payment_histories' => $data
        ];
    }
}

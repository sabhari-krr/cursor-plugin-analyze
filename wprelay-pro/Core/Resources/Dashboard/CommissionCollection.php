<?php

namespace RelayWp\Affiliate\Core\Resources\Dashboard;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\WC;
use RelayWp\Affiliate\Core\Models\Affiliate;

use RelayWp\Affiliate\App\Collection;

class CommissionCollection extends Collection
{
    public function toArray($items, $totalCount, $perPage, $currentPage)
    {
        $data = [];
        foreach ($items as $item) {
            $data[] = [
                'order_id' => $item->order_id,
                'woo_order_id' => $item->woo_order_id,
                'commission_type' => $item->commission_type,
                'affiliate_id' => $item->affiliate_id,
                'affiliate_name' => $item->affiliate_first_name . ' ' . $item->affiliate_last_name,
                'currency' => WC::getWcCurrencySymbol($item->currency),
                'commission_amount' => $item->commission_amount,
                'formatted_commission_amount' => Functions::formatAmount($item->commission_amount, $item->currency),
                'order_amount' => $item->order_amount,
                'formatted_order_amount' => Functions::formatAmount($item->order_amount, $item->currency),
                'status' => ucwords($item->commission_status),
                'is_recurring_order' => (bool)$item->recurring_parent_id
            ];
        }
        return [
            "total" => $totalCount,
            "per_page" => $perPage,
            "total_pages" => ceil($totalCount / $perPage),
            "current_page" => $currentPage,
            'commissions' => $data
        ];
    }
}


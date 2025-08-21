<?php

namespace RelayWp\Affiliate\Core\Resources\Affiliate;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\PluginHelper;
use RelayWp\Affiliate\App\Helpers\WC;
use RelayWp\Affiliate\App\Collection;
use RelayWp\Affiliate\Core\Models\Affiliate;

class AffiliateCommissionCollection extends Collection
{
    public function toArray($items, $totalCount, $perPage, $currentPage)
    {
        $data = [];
        foreach ($items as $item) {
            $data[] = [
                'commission_id' => $item->commission_id,
                'commission_type' => $item->commission_type,
                'order_id' => $item->order_id,
                'woo_order_id' => $item->woo_order_id,
                'customer_id' => $item->customer_id,
                'customer_name' => $item->customer_first_name . ' ' . $item->customer_last_name,
                'customer_email' => $item->customer_email,
                'currency' => WC::getWcCurrencySymbol($item->currency),
                'commission_amount' => $item->commission_amount,
                'formatted_commission_amount' => Functions::formatAmount($item->commission_amount, $item->currency),
                'ordered_at' => $item->ordered_at,
                'order_amount' => $item->order_amount,
                'formatted_order_amount' => Functions::formatAmount($item->order_amount, $item->currency),
                'commission_created_at' => Functions::getWcTime($item->commission_created_at),
                'status' => $item->commission_status,
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


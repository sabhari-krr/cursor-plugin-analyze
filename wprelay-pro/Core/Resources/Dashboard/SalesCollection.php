<?php

namespace RelayWp\Affiliate\Core\Resources\Dashboard;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\WC;
use RelayWp\Affiliate\App\Collection;
use RelayWp\Affiliate\Core\Models\Affiliate;

class SalesCollection extends Collection
{
    public function toArray($items, $totalCount, $perPage, $currentPage)
    {
        $data = [];
        foreach ($items as $item) {
            $data[] = [
                'order_id' => $item->order_id,
                'order_edit_url' => WC::getOrderEditUrl($item->order_id),
                'woo_order_id' => $item->woo_order_id,
                'customer_id' => $item->customer_id,
                'customer_name' => $item->customer_first_name . ' ' . $item->customer_last_name,
                'affiliate_id' => $item->affiliate_id,
                'affiliate_name' => $item->affiliate_first_name . ' ' . $item->affiliate_last_name,
                'currency' => WC::getWcCurrencySymbol($item->currency),
                'amount' => $item->total_amount,
                'medium' => $item->medium ?? 'Link',
                'formatted_amount' => Functions::formatAmount($item->total_amount, $item->currency),
                'ordered_at' => Functions::getWcTime($item->ordered_at),
                'status' => $item->order_status,
                'is_recurring_order' => (bool)$item->recurring_parent_id
            ];
        }
        return [
            "total" => $totalCount,
            "per_page" => $perPage,
            "total_pages" => ceil($totalCount / $perPage),
            "current_page" => $currentPage,
            'sales' => $data
        ];
    }
}


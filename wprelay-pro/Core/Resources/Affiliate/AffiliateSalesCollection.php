<?php

namespace RelayWp\Affiliate\Core\Resources\Affiliate;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\WC;
use RelayWp\Affiliate\App\Collection;
use RelayWp\Affiliate\Core\Models\Affiliate;

class AffiliateSalesCollection extends Collection
{
    public function toArray($items, $affiliate, $member, $totalCount, $perPage, $currentPage)
    {
        $data = [];
        foreach ($items as $item) {
            $data[] = [
                'order_id' => $item->order_id,
                'woo_order_id' => $item->woo_order_id,
                'customer_id' => $item->customer_id,
                'customer_name' => $item->customer_first_name . ' ' . $item->customer_last_name,
                'customer_email' => $item->customer_email,
                'affiliate_id' => $affiliate->id,
                'affiliate_name' => $member->first_name . ' ' . $member->last_name,
                'currency' => WC::getWcCurrencySymbol($item->currency),
                'amount' => $item->total_amount,
                'formatted_amount' => Functions::formatAmount($item->total_amount, $item->currency),
                'program_id' => $item->program_id,
                'ordered_at' => Functions::getWcTime($item->ordered_at),
                'medium' => $item->medium ?? 'link',
                'program_name' => $item->program_name,
                'status' => $item->order_status,
                'is_recurring_order' => (bool)$item->recurring_parent_id
            ];
        }
        return [
            "total" => $totalCount,
            "per_page" => $perPage,
            "current_page" => $currentPage,
            "total_pages" => ceil($totalCount / $perPage),
            'sales' => $data
        ];
    }
}


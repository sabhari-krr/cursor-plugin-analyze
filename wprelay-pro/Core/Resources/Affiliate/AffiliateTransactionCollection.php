<?php

namespace RelayWp\Affiliate\Core\Resources\Affiliate;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\WC;
use RelayWp\Affiliate\App\Collection;

class AffiliateTransactionCollection extends Collection
{
    public function toArray($items, $affiliate, $totalCount, $perPage, $currentPage)
    {
        $data = [];
        foreach ($items as $item) {
            $data[] = [
                'currency' => WC::getWooCommerceCurrencySymbol($item->currency),
                'type' => ucwords($item->type),
                'system_note' => !empty($item->system_note) ? $item->system_note : null,
                'amount' => Functions::formatAmount($item->amount, $item->currency),
                'created_at' => Functions::getWcTime($item->created_at),
                'affiliate_id' => $affiliate->id
            ];
        }
        return [
            "total" => $totalCount,
            "per_page" => $perPage,
            "total_pages" => ceil($totalCount / $perPage),
            "current_page" => $currentPage,
            'transactions' => $data
        ];
    }
}


<?php

namespace RelayWp\Affiliate\Core\Resources\Dashboard;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\Core\Models\Affiliate;
use RelayWp\Affiliate\App\Collection;

class VisitsCollection extends Collection
{
    public function toArray($items, $totalCount, $perPage, $currentPage)
    {
        $data = [];
        foreach ($items as $item) {
            $data[] = [
                'affiliate_id' => $item->affiliate_id,
                'affiliate_name' => $item->affiliate_first_name . ' ' . $item->affiliate_last_name,
                'landing_url' => $item->landing_url,
                'ip_address' => $item->ip_address,
            ];
        }
        return [
            "total" => $totalCount,
            "per_page" => $perPage,
            "total_pages" => ceil($totalCount / $perPage),
            "current_page" => $currentPage,
            'visits' => $data
        ];
    }
}


<?php

namespace RelayWp\Affiliate\Core\Resources\Affiliate;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\PluginHelper;
use RelayWp\Affiliate\App\Collection;
use RelayWp\Affiliate\Core\Models\Affiliate;

class AffiliateCollection extends Collection
{
    public function toArray($items, $affiliate_count, $totalCount, $currentPage, $perPage)
    {
        $data = [];
        foreach ($items as $item) {
            $data[] = [
                'affiliate_id' => $item->affiliate_id,
                'member_id' => $item->member_id,
                'status' => $item->status,
                'first_name' => $item->first_name,
                'last_name' => $item->last_name,
                'url' => Affiliate::getReferralCodeURL($item),
                'email' => $item->email,
                'phone_number' => $item->phone_number,
                'tags' => Functions::jsonDecode($item->tags, true),
                'created_at' => $item->affiliate_created_at,
                'updated_at' => $item->affiliate_updated_at,
            ];
        }

        return [
            "total" => $totalCount,
            "per_page" => $perPage,
            "current_page" => $currentPage,
            "total_pages" => ceil($totalCount / $perPage),
            'affiliates' => $data,
            'affiliate_count' => $affiliate_count
        ];
    }
}


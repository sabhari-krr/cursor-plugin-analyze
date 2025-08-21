<?php

namespace RelayWp\Affiliate\Core\Resources\Program;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\PluginHelper;
use RelayWp\Affiliate\App\Collection;
use RelayWp\Affiliate\Core\Models\Affiliate;
use RelayWp\Affiliate\Core\Models\CommissionTier;
use RelayWp\Affiliate\Core\Models\Program;

class ProgramCommissionTierCollection extends Collection
{
    public function toArray($items, $totalCount, $perPage, $currentPage)
    {
        $data = [];
        foreach ($items as $item) {
            $rateJson = Functions::jsonDecode($item->rate_json ?? null);

            $data[] = [
                "program_id" => $item->program_id,
                "is_expired" => !Program::isValid($item),
                "name" => $item->title,
                'commission_type' => $item->rate_type,
                'commission_tier_info' => CommissionTier::getCommissionTierInfo($item),
                "commission_tier_id" => $item->commission_tier_id,
            ];
        }

        return [
            "total" => $totalCount,
            "per_page" => $perPage,
            "total_pages" => ceil($totalCount / $perPage),
            "current_page" => $currentPage,
            'commission_tiers' => $data
        ];
    }
}


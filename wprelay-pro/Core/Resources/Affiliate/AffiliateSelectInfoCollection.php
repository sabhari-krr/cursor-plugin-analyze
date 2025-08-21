<?php

namespace RelayWp\Affiliate\Core\Resources\Affiliate;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\PluginHelper;
use RelayWp\Affiliate\App\Collection;
use RelayWp\Affiliate\Core\Models\Affiliate;

class AffiliateSelectInfoCollection extends Collection
{
    public function toArray($items)
    {
        $data = [];

        foreach ($items as $item) {
            $data[] = [
                'value' => $item->affiliate_id,
                'label' => $item->email,
            ];
        }

        return [
            'affiliates' => $data
        ];
    }
}


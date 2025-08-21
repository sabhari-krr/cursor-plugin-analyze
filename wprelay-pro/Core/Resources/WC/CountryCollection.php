<?php

namespace RelayWp\Affiliate\Core\Resources\WC;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Collection;

class CountryCollection extends Collection
{

    public function toArray($items)
    {
        $data = [];
        foreach ($items as $countryCode => $countryName) {
            $data[] = [
                "value" => $countryCode,
                "label" => $countryName
            ];
        }

        return $data;
    }
}


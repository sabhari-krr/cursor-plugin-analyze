<?php

namespace RelayWp\Affiliate\Pro\Resources\Rules;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Collection;
use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\Core\Models\Program;
use RelayWp\Affiliate\Pro\Models\Rules;

class RulesCollection extends Collection
{
    public function toArray($program, $items)
    {
        $data = [];
        foreach ($items as $item) {
            $commission_data = Functions::jsonDecode($item->commission_data);
            $data[] = [
                "rule_id" => $item->rule_id,
                "title" => $item->title,
                "description" => $item->description,
                "status" => $item->status,
                "is_active" => $item->status == 'active',
                'start_date' => Functions::formatDate(Functions::utcToWPTime($item->start_date)),
                'end_date' => Functions::formatDate(Functions::utcToWPTime($item->end_date)),
                'type' => $commission_data['type'],
                'based_on' => $commission_data['based_on'],
                'product_ids' => Rules::getProducts($commission_data['product_ids'] ?? []),
                'category_ids' => Rules::getCategories($commission_data['category_ids'] ?? []),
                'value' => $commission_data['value'],
                'is_expired' => !Program::hasHasValidDateRange($item->start_date, $item->end_date),
                'is_scheduled' => Program::isScheduled($item->start_date, $item->end_date),
                'affiliate_ids' => array_map(function ($affiliate) {
                    return [
                        'value' => $affiliate->affiliate_id,
                        'label' => $affiliate->affiliate_email
                    ];
                }, $item->affiliates),
                'affiliates_type' => $item->affiliates_type,
            ];
        }

        return [
            'rules' => $data,
            'program' => [
                'title' =>  $program->title
            ]
        ];
    }
}

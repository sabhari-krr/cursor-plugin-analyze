<?php

namespace RelayWp\Affiliate\Core\Resources\Program;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Collection;
use RelayWp\Affiliate\Core\Models\Affiliate;
use RelayWp\Affiliate\Core\Models\CommissionTier;
use RelayWp\Affiliate\Core\Models\CustomerDiscount;
use RelayWp\Affiliate\Core\Models\Program;

class ProgramCollection extends Collection
{
    public function toArray($items, $rwpCurrency, $totalCount, $perPage, $currentPage)
    {
        $data = [];
        foreach ($items as $item) {
            $data[] = [
                "program_id" => $item->program_id,
                "title" => $item->title,
                "description" => $item->description,
                "status" => $item->status,
                'total_revenue' => Functions::formatAmount($item->total_revenue, $rwpCurrency),
                'total_affiliate' => $item->total_affiliates,
                'affiliate_commission' => Functions::formatAmount($item->affiliate_commissions, $rwpCurrency),
                'customer_discount' => CustomerDiscount::getCustomerDiscountValue($item),
                'commission_type' => CommissionTier::getCommissionTierTypeLabel($item->commission_type),
                //start_date and end_date field is mandatory
                'is_expired' => !Program::hasHasValidDateRange($item->start_date, $item->end_date),
                'is_scheduled' => Program::isScheduled($item->start_date, $item->end_date),
                'is_pro' => Program::isPro($item->base_type)
            ];
        }

        return [
            "total" => $totalCount,
            "per_page" => $perPage,
            "total_pages" => ceil($totalCount / $perPage),
            "current_page" => $currentPage,
            'programs' => $data
        ];
    }
}


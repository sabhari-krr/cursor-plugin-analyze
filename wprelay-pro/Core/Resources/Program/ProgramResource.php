<?php

namespace RelayWp\Affiliate\Core\Resources\Program;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\PluginHelper;
use RelayWp\Affiliate\App\Traits\Conditionable;
use RelayWp\Affiliate\Core\Models\CommissionTier;
use RelayWp\Affiliate\Core\Models\CustomerDiscount;
use RelayWp\Affiliate\Core\Models\Product;
use RelayWp\Affiliate\App\Resource;

class ProgramResource extends Resource
{
    use Conditionable;

    public function toArray($program, $commissionTier, $customerDiscount)
    {
        $rateJson = Functions::jsonDecode($commissionTier->rate_json);
        return [
            'program' =>
            [
                "program_id" => $program->id,
                "title" => $program->title,
                "description" => $program->description,
                "start_date" => Functions::formatDate(Functions::utcToWPTime($program->start_date)),
                "end_date" => Functions::formatDate(Functions::utcToWPTime($program->end_date)),
                "status" => $program->status,
                "is_active" => $program->status == 'active',
                "auto_approve" => (bool)$program->auto_approve,
                'commission_type' => $commissionTier->base_type,
                'commission_sub_type' => $commissionTier->rate_type,
                "commission_type_options" => CommissionTier::getCommissionOptions($commissionTier),
                "is_recurring_commission_enabled" => (bool)$commissionTier->recurring_commission_enabled,
                "recurring_commission_options" => CommissionTier::getRecurringCommissionOptions($commissionTier),
                "customer_discount_type" => $customerDiscount->discount_type,
                "is_pro" => CommissionTier::isProType($commissionTier->base_type),
                "customer_discount_options" => [
                    "date_created" => $customerDiscount->created_at,
                    "value" => $customerDiscount->coupon_amount,
                    "expiry_date" => $customerDiscount->expiry_date,
                    "allow_free_shipping" => (bool)$customerDiscount->free_shipping,
                    "usage_limits" => [
                        'enabled' => (bool)$customerDiscount->usage_limit_enabled,
                        "usage_limit_per_user" => $customerDiscount->usage_limit_per_user,
                        "usage_limit_per_coupon" => $customerDiscount->usage_limit_per_coupon,
                    ],
                    "min_requirements" => [
                        "enabled" => (bool)$customerDiscount->min_requirements_enabled,
                        "minimum_spend" => $customerDiscount->minimum_amount,
                        "maximum_spend" => $customerDiscount->maximum_amount,
                        "individual_use" => (bool)$customerDiscount->individual_use,
                        "exclude_sale_items" => (bool)$customerDiscount->exclude_sale_items,
                        "products" => Product::getProductWithLabels(Functions::jsonDecode($customerDiscount->product_ids)),
                        "exclude_products" => Product::getProductWithLabels(Functions::jsonDecode($customerDiscount->exclude_product_ids)),
                        "categories" => Product::getCategoryWithLabels(Functions::jsonDecode($customerDiscount->category_ids)),
                        "exclude_categories" => Product::getCategoryWithLabels(Functions::jsonDecode($customerDiscount->exclude_category_ids)),
                    ],
                ],
            ]
        ];
    }
}


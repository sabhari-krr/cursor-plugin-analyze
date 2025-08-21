<?php

defined("ABSPATH") or exit;

use RelayWp\Affiliate\Pro\Controllers\Admin\Hooks\CommissionTierController;
use RelayWp\Affiliate\Pro\Controllers\StoreFront\OrderController;
use RelayWp\Affiliate\Pro\Helpers\RulesHelper;
use RelayWp\Affiliate\Pro\Models\Rules;
use RelayWp\Affiliate\Pro\ShortCodes\ShortCodes;
use RelayWp\Affiliate\Pro\ValidationRequest\ProgramRequest;

$store_front_hooks = [
    'actions' => [],
    'filters' => [
        'rwpa_get_commission_details_for_tier_based_type' => ['callable' => [CommissionTierController::class, 'getTierBasedAmountDetail'], 'priority' => 10, 'accepted_args' => 4],
        'rwpa_get_commission_details_for_rule_based_type' => ['callable' => [RulesHelper::class, 'calculateCommissions'], 'priority' => 10, 'accepted_args' => 4],
        'rwpa_get_pro_recursive_data_to_store' => ['callable' => [CommissionTierController::class, 'getProRecursiveData'], 'priority' => 10, 'accepted_args' => 2],
        'rwpa_get_tier_based_commission_tier_options' => ['callable' => [CommissionTierController::class, 'getTierBasedCommissionOptions'], 'priority' => 10, 'accepted_args' => 2],
        'rwpa_track_affiliate_order' => ['callable' => [OrderController::class, 'isRecurringOrder'], 'priority' => 11, 'accepted_args' => 2],
        'rwpa_wprelay_get_active_rules_count' => ['callable' => [Rules::class, 'getActiveRulesCount'], 'priority' => 11, 'accepted_args' => 2],
        'rwpa_wprelay_calculate_bonus_from_rules' => ['callable' => [RulesHelper::class, 'calculateBonusCommission'], 'priority' => 11, 'accepted_args' => 4],
        'rwpa_get_shortcodes_classes' => ['callable' => [ShortCodes::class, 'getShortCodes'], 'priority' => 10, 'accepted_args' => 2],
        'rwp_is_product_falls_in_any_rule' => ['callable' => [RulesHelper::class, 'isProductIsValidForAffiliateLink'], 'priority' => 11, 'accepted_args' => 4]
    ]
];

$admin_hooks = [
    'actions' => [],
    'filters' => [
        'rwpa_create_program_validation_rules' => ['callable' => [ProgramRequest::class, 'addCreateRules'], 'priority' => 11, 'accepted_args' => 2],
        'rwpa_create_program_validation_messages' => ['callable' => [ProgramRequest::class, 'addProgramRequestMessages'], 'priority' => 11, 'accepted_args' => 2]
    ]
];

return [
    'store_front_hooks' => $store_front_hooks,
    'admin_hooks' => $admin_hooks
];

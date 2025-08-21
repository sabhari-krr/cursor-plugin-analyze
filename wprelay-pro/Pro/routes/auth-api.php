<?php

//All routes actions will be performed in Route::handleAuthRequest method.

defined("ABSPATH") or exit;

use RelayWp\Affiliate\Pro\Controllers\Api\PBAREditorController;
use RelayWp\Affiliate\Pro\Controllers\Api\RulesController;
use RelayWp\Affiliate\Pro\Helpers\License;

return [

    'create_rule' => ['callable' => [RulesController::class, 'create']],
    'update_rule' => ['callable' => [RulesController::class, 'update']],
    'delete_rule' => ['callable' => [RulesController::class, 'destroy']],
    'fetch_program_rules' => ['callable' => [RulesController::class, 'index']],
    'rule_update_status' => ['callable' => [RulesController::class, 'updateStatus']],

    'get_licence_details' => ['callable' => [License::class, 'getLicenceDetails']],
    'validate_license' => ['callable' => [License::class, 'validateLicense']],


    //create-program-specific-affiliate-registration
    'save_registration_form_fields' => ['callable' => [PBAREditorController::class, 'updateProgramCustomFields']],
    'get_program_registration_page_info' => ['callable' => [PBAREditorController::class, 'getProgramCustomPageInfo']],
];


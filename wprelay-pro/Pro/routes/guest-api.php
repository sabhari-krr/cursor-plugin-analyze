<?php

use RelayWp\Affiliate\Pro\Controllers\StoreFront\RegisterController;

//All routes actions will be performed in Route::handleAuthRequest method.

defined("ABSPATH") or exit;

return [
    //ISSUE OCC
    'new_affiliate_registration_for_specific_program' => ['callable' => [RegisterController::class, 'newRegistrationForSpecificProgram']],
];

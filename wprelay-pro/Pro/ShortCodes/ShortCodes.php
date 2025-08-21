<?php

namespace RelayWp\Affiliate\Pro\ShortCodes;

defined('ABSPATH') or exit;

class ShortCodes
{
    public static function getShortCodes($classes)
    {
        return array_merge($classes, [
            SpecificProgramRegistrationForm::class
        ]);
    }
}


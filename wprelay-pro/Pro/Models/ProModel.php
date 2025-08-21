<?php

namespace RelayWp\Affiliate\Pro\Models;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Model;

class ProModel extends Model
{

    public function createTable()
    {
        // TODO: Implement createTable() method.
    }

    public static function getProModels($models)
    {
        return array_merge($models, [
            Rules::class,
            RulesMeta::class,
            RulesCommissionDetail::class,
        ]);
    }
}

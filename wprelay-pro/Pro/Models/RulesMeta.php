<?php

namespace RelayWp\Affiliate\Pro\Models;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Model;

class RulesMeta extends Model
{
    protected static $table = 'rules_meta';

    public function createTable()
    {
        $table = static::getTableName();
        $charset = static::getCharSetCollate();

        return "CREATE TABLE {$table} (
                    id BIGINT UNSIGNED AUTO_INCREMENT,
                    model_type VARCHAR(255) NOT NULL,
                    model_id BIGINT UNSIGNED NOT NULL,
                    type VARCHAR(255) NOT NULL,
                    rule_id BIGINT UNSIGNED NOT NULL,
                    PRIMARY KEY (id)
                ) {$charset};";
    }

    public static function syncAffiliates($bonus_id, $request, $delete = false)
    {
        $affiliates = $request->get('affiliate_ids');
        $affiliate_ids = [];


        foreach ($affiliates as $affiliate) {
            $affiliate_id = $affiliate['value'];
            $affiliate_ids[] = $affiliate_id;
        }

        foreach ($affiliate_ids as $affiliate_id) {
            RulesMeta::query()->create([
                'model_type' => 'affiliate',
                'model_id' => $affiliate_id,
                'type' => 'affiliate',
                'rule_id' => $bonus_id
            ]);
        }
    }
}


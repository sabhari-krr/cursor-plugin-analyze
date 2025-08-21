<?php

namespace RelayWp\Affiliate\Pro\Models;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Model;

class RulesCommissionDetail extends Model
{
    protected static $table = 'rules_commission_details';

    public function createTable()
    {
        $table = static::getTableName();
        $charset = static::getCharSetCollate();

        return "CREATE TABLE {$table} (
                    id BIGINT UNSIGNED AUTO_INCREMENT,
                    rule_id BIGINT UNSIGNED,
                    order_id BIGINT UNSIGNED NULL,
                    woo_order_id BIGINT UNSIGNED NULL,
                    commission_amount DECIMAL(15, 2),
                    additional_data json null,
                    created_at timestamp NOT NULL DEFAULT current_timestamp(),
                    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                    deleted_at timestamp NULL,
                    PRIMARY KEY (id)
                ) {$charset};";
    }
}


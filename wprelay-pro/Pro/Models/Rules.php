<?php

namespace RelayWp\Affiliate\Pro\Models;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use RelayWp\Affiliate\App\Model;
use RelayWp\Affiliate\Core\Models\Product;

class Rules extends Model
{
    protected static $table = 'rules';

    public const ACTIVE = 'active';

    public const DRAFT = 'draft';

    public function createTable()
    {
        $table = static::getTableName();
        $charset = static::getCharSetCollate();

        return "CREATE TABLE {$table} (
                    id BIGINT UNSIGNED AUTO_INCREMENT,
                    title TEXT,
                    description TEXT NULL,
                    start_date timestamp null,
                    end_date timestamp null,
                    status varchar(255),
                    program_id BIGINT UNSIGNED NOT NULL,
                    affiliates_type varchar(255) NOT NULL,
                    commission_data json null,
                    additional_info json null,
                    created_at timestamp NOT NULL DEFAULT current_timestamp(),
                    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                    deleted_at timestamp NULL,
                    PRIMARY KEY (id)
                ) {$charset};";
    }

    public static function getCommissionDataFromRequest(Request $request)
    {
        $type = $request->get('type');

        $value = $request->get('value');

        $based_on = $request->get('based_on');

        $commission_data = [
            'type' => $type,
            'value' => $value,
            'based_on' => $based_on,
        ];

        if ($based_on == 'product_in_list' || $based_on == 'product_not_in_list') {
            $commission_data['product_ids'] = static::getProductDataFromRequest($request);
        } else if ($based_on == 'category_in_list' || $based_on == 'category_not_in_list') {
            $commission_data['category_ids'] = static::getCategoryDataFromRequest($request);
        }

        return wp_json_encode($commission_data);
    }


    public static function getAffiliatesForRequest($affiliates)
    {
        $affiliate_data = [];

        foreach ($affiliates as $affiliate) {
            $affiliate_data[] = [
                'label' => $affiliate->email,
                'value' => $affiliate->affiliate_id,
            ];
        }

        return $affiliate_data;
    }

    public static function getProductsForRequest($product_ids)
    {
        return Product::getProductWithLabels($product_ids);
    }

    public static function getCategoriesForRequest($category_ids)
    {
        return Product::getCategoryWithLabels($category_ids);
    }

    public static function getProductDataFromRequest($request)
    {
        $products = $request->get('product_ids');

        if (empty($products)) return null;

        $product_ids = [];
        foreach ($products as $product) {
            $product_ids[] = $product['value'];
        }

        return $product_ids;
    }

    public static function getCategoryDataFromRequest($request)
    {
        $categories = $request->get('category_ids');

        if (empty($categories)) return null;

        $category_ids = [];
        foreach ($categories as $category) {
            $category_ids[] = $category['value'];
        }


        return $category_ids;
    }

    public static function getProducts($products)
    {
        if (empty($products)) return [];
        return static::getProductsForRequest($products);
    }

    public static function getCategories($categories)
    {
        if (empty($categories)) return [];

        return static::getCategoriesForRequest($categories);
    }

    public static function getActiveRulesCount($count, $program)
    {
        $rules = Rules::query()->where("program_id = %d AND status = %s", [$program->id, 'active'])
            ->where("deleted_at is null")
            ->get();

        return count($rules);
    }
}


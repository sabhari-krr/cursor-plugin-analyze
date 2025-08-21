<?php

namespace WPLoyalty\Migration\Core\Controllers\Api;

defined('ABSPATH') or exit;

use WPLoyalty\Migration\App\Helpers\Functions;
use WPLoyalty\Migration\App\Helpers\WordpressHelper;
use WPLoyalty\Migration\Core\Models\MigrationModel;

class DashboardController
{
    private $migrationModel;

    public function __construct()
    {
        $this->migrationModel = new MigrationModel();
    }

    public function playground($request)
    {
        return [
            'message' => 'Migration API is working!',
            'timestamp' => current_time('mysql'),
            'version' => WLMR_VERSION
        ];
    }

    public function getMigrationStatus($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        $status = Functions::getMigrationStatus();
        $progress = Functions::getMigrationProgress();
        
        return [
            'success' => true,
            'status' => $status,
            'progress' => $progress
        ];
    }

    public function getMigrationProgress($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        $progress = Functions::getMigrationProgress();
        $total_items = get_option('wlmr_total_items', 0);
        $processed_items = get_option('wlmr_processed_items', 0);
        
        return [
            'success' => true,
            'progress' => $progress,
            'total_items' => $total_items,
            'processed_items' => $processed_items,
            'remaining_items' => $total_items - $processed_items
        ];
    }

    public function getMigrationHistory($request)
    {
        if (!WordpressHelper::isAdmin()) {
            return ['error' => __('Insufficient permissions.', 'wp-loyalty-migration')];
        }

        $history = Functions::getMigrationHistory();
        $limit = intval($_POST['limit'] ?? 10);
        
        // Limit the history entries
        if ($limit > 0) {
            $history = array_slice($history, 0, $limit);
        }
        
        return [
            'success' => true,
            'history' => $history,
            'total_entries' => count($history)
        ];
    }
}
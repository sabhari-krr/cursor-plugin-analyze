<?php

namespace WLPR\App;

use WLPR\App\Controllers\Common;
use WLPR\App\Controllers\ReminderRuleController;

class Router {
	public static function init() {
		if ( is_admin() ) {
			add_action('admin_menu', [Common::class, 'addMenu']);
			add_action('admin_enqueue_scripts', [Common::class, 'enqueAdminAssets']);
			
			// AJAX actions for reminder rule CRUD
			add_action('wp_ajax_wlpr_create_reminder_rule', [ReminderRuleController::class, 'create']);
			add_action('wp_ajax_wlpr_update_reminder_rule', [ReminderRuleController::class, 'update']);
			add_action('wp_ajax_wlpr_delete_reminder_rule', [ReminderRuleController::class, 'delete']);
			add_action('wp_ajax_wlpr_search_rules', [ReminderRuleController::class, 'search']);
			add_action('wp_ajax_wlpr_bulk_delete_reminder_rules', [ReminderRuleController::class, 'bulkDelete']);
		}
	}


}

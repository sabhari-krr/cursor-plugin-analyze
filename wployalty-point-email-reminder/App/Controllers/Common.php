<?php

namespace WLPR\App\Controllers;

use WLPR\App\Helpers\Input;
use WLPR\App\Helpers\WC;

defined('ABSPATH') or die();

class Common {
    /**
     * Adds a menu page for WPLoyalty plugin reminder if the current user has admin privilege.
     *
     * This method checks if the user has admin privilege using WC::hasAdminPrivilege() method.
     * If the user is an admin, it adds a menu page with the title 'WPLoyalty: Migration' to the WordPress admin menu.
     * The menu page is accessible to users with the 'manage_woocommerce' capability.
     * The menu page callback function is set to self::addMigrationPage().
     *
     * @return void
     */
    public static function addMenu()
    {
        if (WC::hasAdminPrivilege()) {
            add_menu_page(
                __('WPLoyalty: Point Email Reminder', 'wployalty-point-email-reminder'),
                __('WPLoyalty: Point Email Reminder', 'wployalty-point-email-reminder'),
                'manage_woocommerce',
                'wployalty-point-email-reminder',
                [
                    self::class,
                    'addAdminPage'
                ],
                'dashicons-admin-generic',
                30
            );
        }
    }
    
    public static function addAdminPage() {
        if ( !WC::hasAdminPrivilege() ){
            return;
        }
        
        // Initialize params array
        $params = [];
        
        $view = Input::get('view', 'rules');
        
        // Set current_page for navigation
        $params['current_page'] = $view;
        
        switch($view){
            case 'rules':
                // Always show the rules list page, let it handle empty state internally
                $params['main_page']['list'] = self::getListPage();
                break;
            case 'create':
                $type = Input::get('type', '');
                if ($type === 'user') {
                    $params['main_page']['create_user'] = self::getFormUserPage();
                } elseif ($type === 'admin') {
                    $params['main_page']['create_admin'] = self::getFormAdminPage();
                } else {
                    // No type specified, show the selection page
                    $params['main_page']['create'] = self::getCreatePage();
                }
                break;
            case 'edit':
                $type = Input::get('type', 'admin');
                $rule_id = Input::get('id', 0);
                
                if ($type === 'user') {
                    $rule_data = self::getRuleById($rule_id, 'user');
                    $params['main_page']['edit_user'] = self::getFormUserPage($rule_data);
                } else {
                    $rule_data = self::getRuleById($rule_id, 'admin');
                    $params['main_page']['edit_admin'] = self::getFormAdminPage($rule_data);
                }
                break;
            case 'settings':
                $params['main_page']['settings'] = self::getSettingsPage();
                break;
            default:
                // Handle any other views by defaulting to rules
                $params['main_page']['list'] = self::getListPage();
                break;
        }
        
        $file_path = get_theme_file_path('wployalty-point-email-reminder/Admin/main.php');
        if(!file_exists($file_path)){
            $file_path = WLPR_VIEW_PATH . '/Admin/main.php';
        }
        WC::renderTemplate($file_path, $params);
    }

    public static function getRulesPage(){
        $args = [
            'current_page' => 'rules',
            'back_to_apps_url' => admin_url('admin.php?'.http_build_query(['page' => WLR_PLUGIN_SLUG])).'#/apps',
            'previous' => WLPR_PLUGIN_SLUG."Assets/svg/previous.svg",
        ];

        $file_path = get_theme_file_path('wployalty-point-email-reminder/Admin/rules.php');
        if(!file_exists($file_path)){
            $file_path = WLPR_VIEW_PATH . '/Admin/rules.php';
        }
        return WC::renderTemplate($file_path, $args, false);
    }

    public static function enqueAdminAssets(){
        if (Input::get('page') != WLPR_PLUGIN_SLUG) {
            return;
        }
        $suffix = ""; //TODO: Need to generate mins and then replace this with .min
        if (defined("SCRIPT_DEBUG")) {
            $suffix = SCRIPT_DEBUG ? "" : ".min";
        }
        wp_enqueue_style(WLR_PLUGIN_SLUG . '-wlr-font', WLR_PLUGIN_URL . 'Assets/Site/Css/wlr-fonts' . $suffix . '.css', [], WLR_PLUGIN_VERSION . '&t=' . time());
        wp_enqueue_style(WLR_PLUGIN_SLUG . '-alertify', WLR_PLUGIN_URL . 'Assets/Admin/Css/alertify' . $suffix . '.css', [], WLR_PLUGIN_VERSION . '&t=' . time());
        wp_enqueue_style(WLPR_PLUGIN_SLUG . '-main-style', WLPR_PLUGIN_URL . 'Assets/Admin/Css/wlpr-main.css', ['woocommerce_admin_styles'], WLPR_PLUGIN_VERSION . '&t=' . time());

        wp_enqueue_script( WLR_PLUGIN_SLUG . '-alertify', WLR_PLUGIN_URL . 'Assets/Admin/Js/alertify' . $suffix . '.js', [], WLR_PLUGIN_VERSION . '&t=' . time() );
        wp_enqueue_script(WLPR_PLUGIN_SLUG . '-main-script', WLPR_PLUGIN_URL.'Assets/Admin/Js/wlpr-main.js',[
            'jquery',
            'select2'
        ], WLPR_PLUGIN_VERSION . '&t=' . time());

        $localize_data = apply_filters('wlpr_before_localize_data',[
            'ajax_url' => admin_url('admin-ajax.php'),
            'rules_url' => admin_url('admin.php?page=wployalty-point-email-reminder&view=rules'),
            'create_reminder_rule' => WC::createNonce('wlpr_create_reminder_rule_nonce'),
            'update_reminder_rule' => WC::createNonce('wlpr_update_reminder_rule_nonce'),
            'delete_reminder_rule' => WC::createNonce('wlpr_delete_reminder_rule_nonce'),
            'search_nonce' => WC::createNonce('wlpr_search_rules_nonce'),
            'bulk_delete_reminder_rules' => WC::createNonce('wlpr_bulk_delete_reminder_rules_nonce'),
        ]);
        wp_localize_script(WLPR_PLUGIN_SLUG.'-main-script', 'wlpr_localize_data', $localize_data);
    }

    public static function hasRules()
    {
        // Check if there are any email reminder rules using the model
        return \WLPR\App\Models\ScheduledJobs::hasEmailReminderRules();
    }
    
    public static function getListPage()
    {
        $current_page_num = Input::get('paged', 1, 'query', 'int');
        $per_page = Input::get('per_page', 5, 'query', 'int');
        $search_term = Input::get('search', '', 'query', 'text');
        
        // Validate per_page to prevent invalid values
        $allowed_per_pages = [5, 10, 25, 50, 100];
        if (!in_array($per_page, $allowed_per_pages)) {
            $per_page = 5;
        }
        
        $total_rules = \WLPR\App\Models\ScheduledJobs::getEmailReminderRulesCount($search_term);
        $total_pages = ceil($total_rules / $per_page);
        
        // Handle pagination edge case: if current page is beyond total pages and we have rules
        if ($total_rules > 0 && $current_page_num > $total_pages && $total_pages > 0) {
            // Redirect to the last available page
            $redirect_url = add_query_arg([
                'page' => 'wployalty-point-email-reminder',
                'view' => 'rules',
                'paged' => $total_pages,
                'per_page' => $per_page
            ], admin_url('admin.php'));
            
            // Add search term if it exists
            if (!empty($search_term)) {
                $redirect_url = add_query_arg('search', $search_term, $redirect_url);
            }
            
            wp_redirect($redirect_url);
            exit;
        }
        
        // Get rules for current page
        $rules = self::getAllRules($current_page_num, $per_page, $search_term);
        
        // Implement "pull items from next page" logic
        if (!empty($rules) && count($rules) < $per_page && $current_page_num < $total_pages) {
            // Current page is not full and there are more pages
            // Calculate how many more items we need
            $items_needed = $per_page - count($rules);
            
            // Get additional items from next page(s) to fill current page
            $additional_rules = [];
            $next_page = $current_page_num + 1;
            
            while (count($additional_rules) < $items_needed && $next_page <= $total_pages) {
                $next_page_rules = self::getAllRules($next_page, $per_page, $search_term);
                if (!empty($next_page_rules)) {
                    // Take only what we need from this page
                    $take_count = min($items_needed - count($additional_rules), count($next_page_rules));
                    $additional_rules = array_merge($additional_rules, array_slice($next_page_rules, 0, $take_count));
                }
                $next_page++;
            }
            
            // Merge additional rules with current page rules
            $rules = array_merge($rules, $additional_rules);
        }
        
        $args = [
            'rules' => $rules,
            'current_page' => 'rules',
            'search_term' => $search_term, // Pass search term to the view
            'pagination' => [
                'current_page' => $current_page_num,
                'per_page' => $per_page,
                'total_items' => $total_rules,
                'total_pages' => $total_pages,
                'has_prev' => $current_page_num > 1,
                'has_next' => $current_page_num < $total_pages,
                'prev_page' => $current_page_num - 1,
                'next_page' => $current_page_num + 1,
            ]
        ];

        $file_path = get_theme_file_path('wployalty-point-email-reminder/Admin/rules.php');
        if (!file_exists($file_path)) {
            $file_path = WLPR_VIEW_PATH . '/Admin/rules.php';
        }
        return WC::renderTemplate($file_path, $args, false);
    }
    
    public static function getAllRules($page = 1, $per_page = 2, $search_term = '')
    {
        // Fetch email reminder rules from ScheduledJobs model with pagination and search
        $jobs = \WLPR\App\Models\ScheduledJobs::getEmailReminderRules($page, $per_page, $search_term);
        
        if (empty($jobs)) {
            return [];
        }
        
        $rules = [];
        
        // Handle both array and single object results
        if (!is_array($jobs)) {
            $jobs = [$jobs];
        }
        
        foreach ($jobs as $job) {
            // Ensure we have a valid object
            if (!is_object($job)) {
                continue;
            }
            
            // Safely get conditions
            $conditions_json = isset($job->conditions) ? $job->conditions : '{}';
            $conditions = json_decode($conditions_json, true);
            $conditions = $conditions ?: [];
            
            $rule = [
                'id' => isset($job->id) ? $job->id : 0,
                'uid' => isset($job->uid) ? $job->uid : 0,
                'rule_name' => isset($job->rule_name) ? $job->rule_name : '',
                'type' => (isset($job->category) && $job->category === 'admin_report') ? 'admin' : 'user',
                'status' => isset($job->status) ? $job->status : 'pending',
                'created_at' => isset($job->created_at) ? $job->created_at : time(),
                'last_sent' => (isset($job->last_processed_at) && $job->last_processed_at) ? date('Y-m-d H:i:s', $job->last_processed_at) : null,
                'is_recurring' => $conditions['is_recurring'] ?? 0,
                'frequency_type' => $conditions['frequency_type'] ?? '',
                'week_every' => $conditions['week_every'] ?? 1,
                'week_day' => $conditions['week_day'] ?? 'monday',
                'month_every' => $conditions['month_every'] ?? 1,
                'month_date' => $conditions['month_date'] ?? 1,
                'year_date' => $conditions['year_date'] ?? '',
                'schedule_date' => $conditions['schedule_date'] ?? '',
                'batch_limit' => $conditions['batch_limit'] ?? (isset($job->limit) ? $job->limit : 50),
                'include_banned_users' => $conditions['include_banned_users'] ?? 0,
                'report_includes' => $conditions['report_includes'] ?? [],
                'points_includes' => $conditions['points_includes'] ?? [],
                'rewards_includes' => $conditions['rewards_includes'] ?? [],
                'levels_includes' => $conditions['levels_includes'] ?? [],
                'category' => isset($job->category) ? $job->category : 'user_report', // Add category for debugging
            ];
            
            $rules[] = $rule;
        }
        
        return $rules;
    }
    
    public static function getCreatePage()
    {
        $args = [
            'current_page' => 'rules',
            'back_to_apps_url' => admin_url('admin.php?' . http_build_query(['page' => WLR_PLUGIN_SLUG])) . '#/apps',
            'previous' => WLPR_PLUGIN_SLUG . "Assets/svg/previous.svg",
        ];

        $file_path = get_theme_file_path('wployalty-point-email-reminder/Admin/create.php');
        if(!file_exists($file_path)){
            $file_path = WLPR_VIEW_PATH . '/Admin/create.php';
        }
        return WC::renderTemplate($file_path, $args, false);
    }
    public static function getFormAdminPage($rule_data = [])
    {
        // Get current pagination and search state for cancel button
        $current_page = Input::get('paged', 1, 'query', 'int');
        $per_page = Input::get('per_page', 5, 'query', 'int');
        $search_term = Input::get('search', '', 'query', 'text');
        
        $args = [
            'rule_data' => $rule_data ?? [],
            'return_url_params' => [
                'paged' => $current_page,
                'per_page' => $per_page,
                'search' => $search_term
            ]
        ];

        $file_path = get_theme_file_path('wployalty-point-email-reminder/Admin/form-admin.php');
        if (!file_exists($file_path)) {
            $file_path = WLPR_VIEW_PATH . '/Admin/form-admin.php';
        }
        return WC::renderTemplate($file_path, $args, false);
    }

    public static function getFormUserPage($rule_data = [])
    {
        // Get current pagination and search state for cancel button
        $current_page = Input::get('paged', 1, 'query', 'int');
        $per_page = Input::get('per_page', 5, 'query', 'int');
        $search_term = Input::get('search', '', 'query', 'text');
        
        $args = [
            'rule_data' => $rule_data ?? [],
            'return_url_params' => [
                'paged' => $current_page,
                'per_page' => $per_page,
                'search' => $search_term
            ]
        ];

        $file_path = get_theme_file_path('wployalty-point-email-reminder/Admin/form-user.php');
        if (!file_exists($file_path)) {
            $file_path = WLPR_VIEW_PATH . '/Admin/form-user.php';
        }
        return WC::renderTemplate($file_path, $args, false);
    }

    public static function getRuleById($rule_id, $type = 'admin')
    {
        if (empty($rule_id)) {
            return [];
        }
        
        // Fetch rule from ScheduledJobs model
        $job = \WLPR\App\Models\ScheduledJobs::getEmailReminderRuleById($rule_id);
        
        if (empty($job) || !is_object($job)) {
            return [];
        }
        
        // Safely get conditions
        $conditions_json = isset($job->conditions) ? $job->conditions : '{}';
        $conditions = json_decode($conditions_json, true);
        $conditions = $conditions ?: [];
        
        return [
            'id' => isset($job->id) ? $job->id : 0,
            'uid' => isset($job->uid) ? $job->uid : 0,
            'rule_name' => isset($job->rule_name) ? $job->rule_name : '',
            'type' => (isset($job->category) && $job->category === 'admin_report') ? 'admin' : 'user',
            'status' => isset($job->status) ? $job->status : 'pending',
            'created_at' => isset($job->created_at) ? $job->created_at : time(),
            'last_sent' => (isset($job->last_processed_at) && $job->last_processed_at) ? date('Y-m-d H:i:s', $job->last_processed_at) : null,
            'is_recurring' => $conditions['is_recurring'] ?? 0,
            'frequency_type' => $conditions['frequency_type'] ?? '',
            'week_every' => $conditions['week_every'] ?? 1,
            'week_day' => $conditions['week_day'] ?? 'monday',
            'month_every' => $conditions['month_every'] ?? 1,
            'month_date' => $conditions['month_date'] ?? 1,
            'year_date' => $conditions['year_date'] ?? '',
            'schedule_date' => $conditions['schedule_date'] ?? '',
            'batch_limit' => $conditions['batch_limit'] ?? (isset($job->limit) ? $job->limit : 50),
            'include_banned_users' => $conditions['include_banned_users'] ?? 0,
            'report_includes' => $conditions['report_includes'] ?? [],
            'points_includes' => $conditions['points_includes'] ?? [],
            'rewards_includes' => $conditions['rewards_includes'] ?? [],
            'levels_includes' => $conditions['levels_includes'] ?? [],
            'category' => isset($job->category) ? $job->category : 'user_report', // Add category for debugging
        ];
    }

    public static function getSettingsPage()
    {
        $args = [
            'current_page' => 'settings',
        ];

        $file_path = get_theme_file_path('wployalty-point-email-reminder/Admin/settings.php');
        if (!file_exists($file_path)) {
            $file_path = WLPR_VIEW_PATH . '/Admin/settings.php';
        }
        return WC::renderTemplate($file_path, $args, false);
    }
}
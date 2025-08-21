<?php

namespace WLPR\App\Controllers;

use WLPR\App\Models\ScheduledJobs;
use WLPR\App\Helpers\WC;
use WLPR\App\Helpers\Input;
use WLPR\App\Helpers\Validation;

defined('ABSPATH') or die();

class ReminderRuleController
{
    public static function create()
    {
        if (!WC::isSecurityValid('wlpr_create_reminder_rule_nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'wployalty-point-email-reminder')]);
        }

        // Retrieve data from request
        $category = Input::get('category', '', 'post', 'text');
        $rule_name = Input::get('rule_name', '', 'post', 'text');
        $batch_limit = Input::get('batch_limit', 50, 'post', 'int');
        $is_recurring = Input::get('is_recurring', 0, 'post', 'int');
        $schedule_date = Input::get('schedule_date', '', 'post', 'text');
        
        // Build conditions array from form data based on category
        if ($category === 'admin_report') {
            $conditions = self::buildAdminConditions();
        } else {
            $conditions = self::buildUserConditions();
        }

        // Prepare data for database
        $data = [
            'uid' => ScheduledJobs::getMaxUid(),
            'source_app' => 'wlr_email_reminder',
            'admin_mail' => WC::getLoginUserEmail(),
            'rule_name' => $rule_name,
            'category' => $category,
            'action_type' => 'email_reminder',
            'conditions' => json_encode($conditions),
            'status' => 'active',
            'limit' => $batch_limit,
            'offset' => 0,
            'created_at' => current_time('timestamp'),
            'updated_at' => current_time('timestamp'),
        ];

        // Validate data
        $validate_data = Validation::validateReminderRule($data);
        if (is_array($validate_data) && !empty($validate_data)) {
            foreach ($validate_data as $key => $validate) {
                $validate_data[$key] = current($validate);
            }
            wp_send_json_error([
                'error_fields' => $validate_data,
                'message' => __('Basic validation failed', 'wployalty-point-email-reminder')
            ]);
        }

        // Validate schedule conditions
        $schedule_validate_data = Validation::validateScheduleConditions($conditions, $category);
        if (is_array($schedule_validate_data) && !empty($schedule_validate_data)) {
            foreach ($schedule_validate_data as $key => $validate) {
                $schedule_validate_data[$key] = current($validate);
            }
            wp_send_json_error([
                'error_fields' => $schedule_validate_data,
                'message' => __('Schedule validation failed', 'wployalty-point-email-reminder')
            ]);
        }

        // Check if Action Scheduler is available
        if (!function_exists('as_next_scheduled_action')) {
            wp_send_json_error(['message' => __('Action Scheduler plugin is required for scheduling', 'wployalty-point-email-reminder')]);
        }

        // Check if schedule already exists
        $schedule_action_event_name = self::generateScheduleActionName($category, $conditions);
        $schedule_event_arg = [
            'rule_category' => $category,
            'rule_conditions' => $conditions,
            'batch_limit' => $batch_limit
        ];

        // Check if this schedule action already exists
        if (as_next_scheduled_action($schedule_action_event_name, [$schedule_event_arg])) {
            wp_send_json_error(['message' => __('This schedule action already exists for this configuration', 'wployalty-point-email-reminder')]);
        }

        // Insert into database
        $model = new ScheduledJobs();
        $inserted_id = $model->insertReminderRule($data);

        if ($inserted_id) {
            // Create schedule if recurring or if schedule_date is set
            if ($is_recurring || !empty($schedule_date)) {
                self::createSchedule($schedule_action_event_name, $schedule_event_arg, $conditions);
            }
            
            wp_send_json_success(['message' => __('Rule created successfully', 'wployalty-point-email-reminder')]);
        } else {
            wp_send_json_error(['message' => __('Failed to create rule', 'wployalty-point-email-reminder')]);
        }
    }

    public static function update()
    {
        if (!WC::isSecurityValid('wlpr_update_reminder_rule_nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'wployalty-point-email-reminder')]);
        }

        $id = Input::get('id', 0, 'post', 'int');
        $category = Input::get('category');
        
        // Build conditions array from form data based on category
        if ($category === 'admin_report') {
            $conditions = self::buildAdminConditions();
        } else {
            $conditions = self::buildUserConditions();
        }

        $post = [
            'source_app' => 'wlr_email_reminder',
            'admin_mail' => WC::getLoginUserEmail(),
            'rule_name' => Input::get('rule_name', '', 'post', 'text'),
            'category' => $category,
            'action_type' => 'email_reminder',
            'conditions' => json_encode($conditions),
            'status' => 'active',
            'limit' => Input::get('batch_limit', 50, 'post', 'int'),
            'updated_at' => current_time('timestamp'),
        ];

        $validate_data = Validation::validateReminderRule($post);
        if (is_array($validate_data) && !empty($validate_data)) {
            foreach ($validate_data as $key => $validate) {
                $validate_data[$key] = current($validate);
            }
            wp_send_json_error([
                'field_error' => $validate_data,
                'message' => __('Invalid fields', 'wployalty-point-email-reminder')
            ]);
        }

        $model = new ScheduledJobs();
        $result = $model->updateReminderRule($id, $post);

        if ($result) {
            wp_send_json_success(['message' => __('Rule updated successfully', 'wployalty-point-email-reminder')]);
        } else {
            wp_send_json_error(['message' => __('Failed to update rule', 'wployalty-point-email-reminder')]);
        }
    }

    public static function delete()
    {
        if (!WC::isSecurityValid('wlpr_delete_reminder_rule_nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'wployalty-point-email-reminder')]);
        }

        $id = Input::get('id', 0, 'post', 'int');
        $model = new ScheduledJobs();
        $result = $model->deleteReminderRule($id);

        if ($result) {
            wp_send_json_success(['message' => __('Rule deleted successfully', 'wployalty-point-email-reminder')]);
        } else {
            wp_send_json_error(['message' => __('Failed to delete rule', 'wployalty-point-email-reminder')]);
        }
    }

    public static function search()
    {
        // Verify nonce
        if (!WC::isSecurityValid('wlpr_search_rules_nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'wployalty-point-email-reminder')]);
            exit;
        }

        // Get search parameters
        $search_term = Input::get('search_term', '', 'post', 'text');
        $page = Input::get('page', 1, 'post', 'int');
        $per_page = Input::get('per_page', 5, 'post', 'int');

        // Get rules with search
        $rules = \WLPR\App\Controllers\Common::getAllRules($page, $per_page, $search_term);
        $total_rules = \WLPR\App\Models\ScheduledJobs::getEmailReminderRulesCount($search_term);
        $total_pages = ceil($total_rules / $per_page);

        // Prepare pagination data
        $pagination = [
            'current_page' => $page,
            'per_page' => $per_page,
            'total_items' => $total_rules,
            'total_pages' => $total_pages,
            'has_prev' => $page > 1,
            'has_next' => $page < $total_pages,
            'prev_page' => $page - 1,
            'next_page' => $page + 1,
        ];

        // Pass the search term to the view
        $search_term_for_view = $search_term;

        // Render the rules table HTML
        ob_start();
        include(WLPR_VIEW_PATH . '/Admin/rules-table.php');
        $table_html = ob_get_clean();

        // Render the pagination HTML
        ob_start();
        // Set variables needed for pagination rendering
        $pagination = $pagination;
        $search_term = $search_term;
        include(WLPR_VIEW_PATH . '/Admin/rules-pagination.php');
        $pagination_html = ob_get_clean();

        // Send JSON response and exit
        wp_send_json_success([
            'table_html' => $table_html,
            'pagination_html' => $pagination_html,
            'pagination' => $pagination,
            'total_rules' => $total_rules,
            'search_term' => $search_term
        ]);
        exit;
    }

    public static function bulkDelete()
    {
        // Verify nonce
        if (!WC::isSecurityValid('wlpr_bulk_delete_reminder_rules_nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'wployalty-point-email-reminder')]);
            exit;
        }

        // Get rule IDs using Input helper
        $rule_ids = Input::get('rule_ids', [], 'post', false);
        
        if (empty($rule_ids) || !is_array($rule_ids)) {
            wp_send_json_error(['message' => __('No rules selected for deletion', 'wployalty-point-email-reminder')]);
            exit;
        }

        // Validate that all IDs are integers
        $valid_ids = [];
        foreach ($rule_ids as $id) {
            $valid_id = intval($id);
            if ($valid_id > 0) {
                $valid_ids[] = $valid_id;
            }
        }

        if (empty($valid_ids)) {
            wp_send_json_error(['message' => __('Invalid rule IDs provided', 'wployalty-point-email-reminder')]);
            exit;
        }

        $model = new ScheduledJobs();
        $deleted_count = 0;
        $failed_count = 0;
        $failed_ids = [];

        foreach ($valid_ids as $id) {
            $result = $model->deleteReminderRule($id);
            if ($result) {
                $deleted_count++;
            } else {
                $failed_count++;
                $failed_ids[] = $id;
            }
        }

        if ($deleted_count > 0) {
            // WordPress standard: Show success count with proper i18n
            $message = sprintf(
                _n(
                    '%s rule deleted successfully.',
                    '%s rules deleted successfully.',
                    $deleted_count,
                    'wployalty-point-email-reminder'
                ),
                number_format_i18n($deleted_count)
            );
            
            // Add failure count if any failed
            if ($failed_count > 0) {
                $message .= ' ' . sprintf(
                    _n(
                        '%s rule could not be deleted.',
                        '%s rules could not be deleted.',
                        $failed_count,
                        'wployalty-point-email-reminder'
                    ),
                    number_format_i18n($failed_count)
                );
            }
            
            wp_send_json_success([
                'message' => $message,
                'deleted_count' => $deleted_count,
                'failed_count' => $failed_count,
                'total_count' => count($valid_ids)
            ]);
        } else {
            wp_send_json_error([
                'message' => __('No rules were deleted. Please try again.', 'wployalty-point-email-reminder'),
                'deleted_count' => 0,
                'failed_count' => $failed_count,
                'total_count' => count($valid_ids)
            ]);
        }
        exit;
    }

    /**
     * Build conditions array for admin form
     * @return array
     */
    private static function buildAdminConditions()
    {
        return [
            'is_recurring' => Input::get('is_recurring', 0, 'post', 'int'),
            'frequency_type' => Input::get('frequency_type', 'week'),
            'week_every' => Input::get('week_every', 1, 'post', 'int'),
            'week_day' => Input::get('week_day', 'monday'),
            'month_every' => Input::get('month_every', 1, 'post', 'int'),
            'month_date' => Input::get('month_date', 1),
            'year_date' => Input::get('year_date', ''),
            'schedule_date' => Input::get('schedule_date', ''),
            'batch_limit' => Input::get('batch_limit', 50, 'post', 'int'),
            'include_banned_users' => Input::get('include_banned_users', 0, 'post', 'int'),
            'report_includes' => Input::get('report_includes', [], 'post'),
        ];
    }

    /**
     * Build conditions array for user form
     * @return array
     */
    private static function buildUserConditions()
    {
        $user_report_includes = Input::get('user_report_includes', [], 'post');
        
        // Separate user report includes by category
        $points_includes = [];
        $rewards_includes = [];
        $levels_includes = [];
        
        foreach ($user_report_includes as $include) {
            if (strpos($include, 'points_') === 0) {
                $points_includes[] = $include;
            } elseif (strpos($include, 'rewards_') === 0) {
                $rewards_includes[] = $include;
            } elseif (strpos($include, 'levels_') === 0) {
                $levels_includes[] = $include;
            }
        }
        
        return [
            'is_recurring' => Input::get('is_recurring', 0, 'post', 'int'),
            'frequency_type' => Input::get('user_frequency_type', 'week'),
            'week_every' => Input::get('week_every', 1, 'post', 'int'),
            'week_day' => Input::get('week_day', 'monday'),
            'month_every' => Input::get('month_every', 1, 'post', 'int'),
            'month_date' => Input::get('month_date', 1),
            'year_date' => Input::get('year_date', ''),
            'schedule_date' => Input::get('schedule_date', ''),
            'batch_limit' => Input::get('batch_limit', 50, 'post', 'int'),
            'points_includes' => $points_includes,
            'rewards_includes' => $rewards_includes,
            'levels_includes' => $levels_includes,
            'user_report_includes' => $user_report_includes, // Keep original array as well
        ];
    }

    /**
     * Generate unique schedule action name
     * @param string $category
     * @param array $conditions
     * @return string
     */
    private static function generateScheduleActionName($category, $conditions)
    {
        $frequency_type = $conditions['frequency_type'] ?? 'week';
        $schedule_date = $conditions['schedule_date'] ?? '';
        $is_recurring = $conditions['is_recurring'] ?? 0;
        
        if ($is_recurring) {
            return "wlpr_{$category}_recurring_{$frequency_type}";
        } else {
            $date_part = !empty($schedule_date) ? date('Y_m_d', strtotime($schedule_date)) : 'once';
            return "wlpr_{$category}_{$date_part}";
        }
    }

    /**
     * Create schedule action
     * @param string $action_name
     * @param array $args
     * @param array $conditions
     */
    private static function createSchedule($action_name, $args, $conditions)
    {
        if (!function_exists('as_schedule_recurring_action') || !function_exists('as_schedule_single_action')) {
            return;
        }

        $is_recurring = $conditions['is_recurring'] ?? 0;
        $schedule_date = $conditions['schedule_date'] ?? '';
        $frequency_type = $conditions['frequency_type'] ?? 'week';
        
        if ($is_recurring) {
            // Calculate recurring schedule
            $interval = self::calculateRecurringInterval($conditions);
            $start_time = time() + 10 * 60; // Start in 10 minutes
            
            as_schedule_recurring_action($start_time, $interval, $action_name, [$args]);
        } else {
            // Single schedule
            if (!empty($schedule_date)) {
                $schedule_timestamp = strtotime($schedule_date);
                if ($schedule_timestamp > time()) {
                    as_schedule_single_action($schedule_timestamp, $action_name, [$args]);
                }
            }
        }
    }

    /**
     * Calculate recurring interval in seconds
     * @param array $conditions
     * @return int
     */
    private static function calculateRecurringInterval($conditions)
    {
        $frequency_type = $conditions['frequency_type'] ?? 'week';
        $week_every = $conditions['week_every'] ?? 1;
        $month_every = $conditions['month_every'] ?? 1;
        
        // Define constants if not already defined
        if (!defined('WEEK_IN_SECONDS')) {
            define('WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS);
        }
        if (!defined('YEAR_IN_SECONDS')) {
            define('YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS);
        }
        
        switch ($frequency_type) {
            case 'week':
                return $week_every * WEEK_IN_SECONDS;
            case 'month':
                return $month_every * 30 * DAY_IN_SECONDS; // Approximate
            case 'year':
                return YEAR_IN_SECONDS;
            default:
                return WEEK_IN_SECONDS;
        }
    }
} 
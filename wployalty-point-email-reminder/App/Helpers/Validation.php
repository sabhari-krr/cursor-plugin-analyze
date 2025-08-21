<?php

namespace WLPR\App\Helpers;

use Valitron\Validator;

defined('ABSPATH') or die();

class Validation
{
    /**
     * Validate reminder rule data
     * 
     * @param array $post
     * @return array|true
     */
    public static function validateReminderRule($post)
    {
        $rule_validator = new Validator($post);
        $labels = [];
        $labels_fields = [
            'rule_name',
            'category',
            'action_type',
            'conditions',
            'status',
            'limit',
            'offset'
        ];
        $this_field = __('This field', 'wployalty-point-email-reminder');
        foreach ($labels_fields as $label) {
            $labels[$label] = $this_field;
        }
        $rule_validator->labels($labels);
        $rule_validator->stopOnFirstFail(false);
        
        $rule_validator->rule('required', [
            'rule_name',
            'category',
            'action_type',
            'conditions',
            'status'
        ])->message(__('{field} is required', 'wployalty-point-email-reminder'));
        
        $rule_validator->rule('lengthBetween', 'rule_name', [1, 255])
            ->message(__('Rule name must be between 1 and 255 characters', 'wployalty-point-email-reminder'));
            
        $rule_validator->rule('in', 'category', ['admin_report', 'user_report'])
            ->message(__('Invalid category selected', 'wployalty-point-email-reminder'));
            
        $rule_validator->rule('in', 'status', ['active', 'inactive'])
            ->message(__('Invalid status selected', 'wployalty-point-email-reminder'));
            
        $rule_validator->rule('integer', ['limit', 'offset'])
            ->message(__('{field} must be a number', 'wployalty-point-email-reminder'));
            
        $rule_validator->rule('min', 'limit', 1)
            ->message(__('Limit must be 1 or greater', 'wployalty-point-email-reminder'));
            
        $rule_validator->rule('min', 'offset', 0)
            ->message(__('Offset must be 0 or greater', 'wployalty-point-email-reminder'));

        if ($rule_validator->validate()) {
            return true;
        }
        return $rule_validator->errors();
    }

    /**
     * Validate schedule conditions data
     * 
     * @param array $conditions
     * @param string $category
     * @return array|true
     */
    public static function validateScheduleConditions($conditions, $category)
    {
        $schedule_validator = new Validator($conditions);
        $schedule_validator->stopOnFirstFail(false);
        
        // Common validation rules
        $schedule_validator->rule('required', ['frequency_type', 'batch_limit'])
            ->message(__('{field} is required', 'wployalty-point-email-reminder'));
            
        $schedule_validator->rule('in', 'frequency_type', ['week', 'month', 'year'])
            ->message(__('Invalid frequency type selected', 'wployalty-point-email-reminder'));
            
        $schedule_validator->rule('integer', ['is_recurring', 'week_every', 'month_every', 'batch_limit'])
            ->message(__('{field} must be a number', 'wployalty-point-email-reminder'));
            
        $schedule_validator->rule('min', 'batch_limit', 1)
            ->message(__('Batch limit must be 1 or greater', 'wployalty-point-email-reminder'));
            
        $schedule_validator->rule('min', 'week_every', 1)
            ->message(__('Week every must be 1 or greater', 'wployalty-point-email-reminder'));
            
        $schedule_validator->rule('min', 'month_every', 1)
            ->message(__('Month every must be 1 or greater', 'wployalty-point-email-reminder'));
            
        $schedule_validator->rule('in', 'week_day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])
            ->message(__('Invalid week day selected', 'wployalty-point-email-reminder'));

        // Category-specific validation
        if ($category === 'admin_report') {
            $schedule_validator->rule('required', 'report_includes')
                ->message(__('Report includes is required for admin reports', 'wployalty-point-email-reminder'));
                
            if (!empty($conditions['report_includes']) && !is_array($conditions['report_includes'])) {
                return ['report_includes' => [__('Report includes must be an array', 'wployalty-point-email-reminder')]];
            }
        } elseif ($category === 'user_report') {
            $schedule_validator->rule('required', 'user_report_includes')
                ->message(__('User report includes is required for user reports', 'wployalty-point-email-reminder'));
                
            if (!empty($conditions['user_report_includes']) && !is_array($conditions['user_report_includes'])) {
                return ['user_report_includes' => [__('User report includes must be an array', 'wployalty-point-email-reminder')]];
            }
        }

        // Schedule date validation for non-recurring rules
        if (empty($conditions['is_recurring']) && empty($conditions['schedule_date'])) {
            return ['schedule_date' => [__('Schedule date is required for non-recurring rules', 'wployalty-point-email-reminder')]];
        }

        if ($schedule_validator->validate()) {
            return true;
        }
        return $schedule_validator->errors();
    }
} 
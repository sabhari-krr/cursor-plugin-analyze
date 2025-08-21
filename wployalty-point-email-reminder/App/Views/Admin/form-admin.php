<?php
defined('ABSPATH') or die();

// Determine if this is edit mode
$is_edit = isset($rule_data) && !empty($rule_data);
$rule_id = $is_edit ? $rule_data['id'] : 0;
$form_title = $is_edit ? __('Edit Admin Report', 'wployalty-point-email-reminder') : __('Schedule Admin Report', 'wployalty-point-email-reminder');
$button_text = $is_edit ? __('Update', 'wployalty-point-email-reminder') : __('Save', 'wployalty-point-email-reminder');
$form_action = $is_edit ? 'wlpr_update_admin_reminder' : 'wlpr_save_admin_reminder';
?>

<div class="wlpr-create-form-container">
    <!-- Form Header with Save/Cancel buttons -->
    <div class="wlpr-form-header">
        <h2 class="wlpr-form-title"><?php echo $form_title; ?></h2>
        <div class="wlpr-form-actions">
            <?php
            // Build return URL with pagination and search parameters
            $return_args = ['page' => 'wployalty-point-email-reminder', 'view' => 'rules'];
            if (isset($return_url_params)) {
                if (!empty($return_url_params['paged']) && $return_url_params['paged'] > 1) {
                    $return_args['paged'] = $return_url_params['paged'];
                }
                if (!empty($return_url_params['per_page'])) {
                    $return_args['per_page'] = $return_url_params['per_page'];
                }
                if (!empty($return_url_params['search'])) {
                    $return_args['search'] = $return_url_params['search'];
                }
            }
            ?>
            <a href="<?php echo admin_url('admin.php?' . http_build_query($return_args)); ?>" class="wlpr-button-action non-colored-button">
                <?php _e('Cancel', 'wployalty-point-email-reminder'); ?>
            </a>
            <button type="submit" form="wlpr-admin-form" class="wlpr-button-action colored-button" id="wlpr-admin-submit">
                <?php echo $button_text; ?>
            </button>
        </div>
    </div>

    <!-- Form Content -->
    <form id="wlpr-admin-form" method="post">
        <input type="hidden" name="action" value="<?php echo $is_edit ? 'wlpr_update_reminder_rule' : 'wlpr_create_reminder_rule'; ?>">
        <input type="hidden" name="wlpr_nonce" value="<?php echo wp_create_nonce($is_edit ? 'wlpr_update_reminder_rule_nonce' : 'wlpr_create_reminder_rule_nonce'); ?>">
        <input type="hidden" name="category" value="admin_report">
        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo esc_attr($rule_id); ?>">
        <?php endif; ?>

        <!-- Rule Name Field -->
        <div class="wlpr-data-content-row">
            <label for="rule_name" class="wlpr-data-content-label">
                <?php _e('Rule Name', 'wployalty-point-email-reminder'); ?> <span class="required">*</span>
            </label>
            <div class="wlpr-data-content-field">
                <input type="text" id="rule_name" name="rule_name" 
                       value="<?php echo $is_edit ? esc_attr($rule_data['rule_name'] ?? '') : ''; ?>"
                       placeholder="<?php _e('Enter a descriptive name for this rule', 'wployalty-point-email-reminder'); ?>"
                       maxlength="255">
            </div>
        </div>

        <div class="wlpr-body-data">
            <!-- Report Should Include Field (Accordion) -->
            <div class="wlpr-data-content-row">
                <div class="wlpr-accordion">
                    <div class="wlpr-accordion-header" id="report-accordion-header">
                        <label class="wlpr-data-content-label">
                            <?php _e('Report should include', 'wployalty-point-email-reminder'); ?>
                            <span class="wlpr-selected-count">(<?php echo $is_edit ? count($rule_data['report_includes'] ?? []) : '1'; ?> selected)</span>
                        </label>
                        <div class="wlpr-accordion-toggle">
                            <i class="dashicons dashicons-arrow-down-alt2"></i>
                        </div>
                    </div>
                    <div class="wlpr-accordion-content" id="report-accordion-content">
                        <div class="wlpr-report-options">
                            <?php 
                            $report_options = [
                                'new_customers' => __('X number of users became loyalty customer in the past XY days', 'wployalty-point-email-reminder'),
                                'points_earned' => __('X points earned in the past XY days', 'wployalty-point-email-reminder'),
                                'points_redeemed' => __('X points redeemed in the past XY days', 'wployalty-point-email-reminder'),
                                'rewards_earned' => __('X rewards earned in the past XY days', 'wployalty-point-email-reminder'),
                                'rewards_redeemed' => __('X rewards redeemed in the past XY days', 'wployalty-point-email-reminder'),
                                'orders_placed' => __('X orders placed using wployalty in the past XY days', 'wployalty-point-email-reminder'),
                                'orders_value' => __('X value of orders placed using wployalty in the past XY days (orders placed using wployalty coupons)', 'wployalty-point-email-reminder')
                            ];
                            
                            $selected_reports = $is_edit ? ($rule_data['report_includes'] ?? []) : ['new_customers'];
                            
                            foreach ($report_options as $value => $label):
                                $checked = in_array($value, $selected_reports) ? 'checked' : '';
                            ?>
                                <label>
                                    <input type="checkbox" name="report_includes[]" value="<?php echo esc_attr($value); ?>" <?php echo $checked; ?>>
                                    <?php echo $label; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Is Recurring Field -->
            <div class="wlpr-data-content-row">
                <label class="wlpr-data-content-label">
                    <?php _e('Is recurring?', 'wployalty-point-email-reminder'); ?>
                </label>
                <div class="wlpr-data-content-field">
                    <label>
                        <input type="checkbox" id="is_recurring" name="is_recurring" value="1" 
                               <?php echo ($is_edit && !empty($rule_data['is_recurring'])) ? 'checked' : ''; ?>>
                        <?php _e('Enable recurring schedule', 'wployalty-point-email-reminder'); ?>
                    </label>
                </div>
            </div>

            <!-- One-time Date Picker (shown when not recurring) -->
            <div class="wlpr-data-content-row" id="one_time_date_row" style="display: <?php echo ($is_edit && empty($rule_data['is_recurring'])) ? 'block' : 'none'; ?>;">
                <label for="schedule_date" class="wlpr-data-content-label">
                    <?php _e('Select date', 'wployalty-point-email-reminder'); ?>
                </label>
                <div class="wlpr-data-content-field">
                    <input type="date" id="schedule_date" name="schedule_date" 
                           min="<?php echo date('Y-m-d'); ?>" onkeydown="return false;"
                           value="<?php echo $is_edit ? esc_attr($rule_data['schedule_date'] ?? '') : ''; ?>">
                </div>
            </div>

            <!-- Recurring Options (shown when recurring is checked) -->
            <div id="recurring_options" style="display: <?php echo ($is_edit && !empty($rule_data['is_recurring'])) ? 'block' : 'none'; ?>;">
                
                <!-- Frequency Type -->
                <div class="wlpr-data-content-row">
                    <label class="wlpr-data-content-label">
                        <?php _e('Frequency Type', 'wployalty-point-email-reminder'); ?>
                    </label>
                    <div class="wlpr-data-content-field">
                        <?php 
                        $frequency_type = $is_edit ? ($rule_data['frequency_type'] ?? 'week') : 'week';
                        ?>
                        <label>
                            <input type="radio" name="frequency_type" value="week" id="freq_week" 
                                   <?php echo ($frequency_type === 'week') ? 'checked' : ''; ?>>
                            <?php _e('Week', 'wployalty-point-email-reminder'); ?>
                        </label><br>
                        <label>
                            <input type="radio" name="frequency_type" value="month" id="freq_month" 
                                   <?php echo ($frequency_type === 'month') ? 'checked' : ''; ?>>
                            <?php _e('Month', 'wployalty-point-email-reminder'); ?>
                        </label><br>
                        <label>
                            <input type="radio" name="frequency_type" value="year" id="freq_year" 
                                   <?php echo ($frequency_type === 'year') ? 'checked' : ''; ?>>
                            <?php _e('Year', 'wployalty-point-email-reminder'); ?>
                        </label>
                    </div>
                </div>

                <!-- Week Options -->
                <div id="week_options" class="wlpr-frequency-options" style="display: <?php echo ($frequency_type === 'week') ? 'block' : 'none'; ?>;">
                    <div class="wlpr-data-content-row">
                        <div class="wlpr-row-filed">
                            <div>
                                <label for="week_every" class="wlpr-data-content-label">
                                    <?php _e('Every', 'wployalty-point-email-reminder'); ?>
                                </label>
                                <div class="wlpr-data-content-field">
                                    <select id="week_every" name="week_every">
                                        <?php 
                                        $week_every = $is_edit ? ($rule_data['week_every'] ?? 1) : 1;
                                        for ($i = 1; $i <= 4; $i++): 
                                        ?>
                                            <option value="<?php echo $i; ?>" <?php selected($week_every, $i); ?>>
                                                <?php 
                                                $suffix = '';
                                                if ($i == 1) {
                                                    $suffix = 'st';
                                                } elseif ($i == 2) {
                                                    $suffix = 'nd';
                                                } elseif ($i == 3) {
                                                    $suffix = 'rd';
                                                } else {
                                                    $suffix = 'th';
                                                }
                                                echo sprintf(__('%d%s week', 'wployalty-point-email-reminder'), $i, $suffix);
                                                ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label for="week_day" class="wlpr-data-content-label">
                                    <?php _e('Day', 'wployalty-point-email-reminder'); ?>
                                </label>
                                <div class="wlpr-data-content-field">
                                    <select id="week_day" name="week_day">
                                        <?php 
                                        $week_day = $is_edit ? ($rule_data['week_day'] ?? 'monday') : 'monday';
                                        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                                        foreach ($days as $day): 
                                        ?>
                                            <option value="<?php echo $day; ?>" <?php selected($week_day, $day); ?>>
                                                <?php _e(ucfirst($day), 'wployalty-point-email-reminder'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Month Options -->
                <div id="month_options" class="wlpr-frequency-options" style="display: <?php echo ($frequency_type === 'month') ? 'block' : 'none'; ?>;">
                    <div class="wlpr-data-content-row">
                        <div class="wlpr-row-filed">
                            <div>
                                <label for="month_every" class="wlpr-data-content-label">
                                    <?php _e('Every', 'wployalty-point-email-reminder'); ?>
                                </label>
                                <div class="wlpr-data-content-field">
                                    <select id="month_every" name="month_every">
                                        <?php 
                                        $month_every = $is_edit ? ($rule_data['month_every'] ?? 1) : 1;
                                        for($i = 1; $i <= 12; $i++): 
                                        ?>
                                            <option value="<?php echo $i; ?>" <?php selected($month_every, $i); ?>>
                                                <?php echo sprintf(_n('%d month', '%d months', $i, 'wployalty-point-email-reminder'), $i); ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label for="month_date" class="wlpr-data-content-label">
                                    <?php _e('Date', 'wployalty-point-email-reminder'); ?>
                                </label>
                                <div class="wlpr-data-content-field">
                                    <select id="month_date" name="month_date">
                                        <?php 
                                        $month_date = $is_edit ? ($rule_data['month_date'] ?? 1) : 1;
                                        for($i = 1; $i <= 28; $i++): 
                                        ?>
                                            <option value="<?php echo $i; ?>" <?php selected($month_date, $i); ?>>
                                                <?php echo sprintf(_x('%d%s of the month', 'ordinal date', 'wployalty-point-email-reminder'), $i, 
                                                    ($i == 1 ? 'st' : ($i == 2 ? 'nd' : ($i == 3 ? 'rd' : 'th')))); ?>
                                            </option>
                                        <?php endfor; ?>
                                        <option value="end" <?php selected($month_date, 'end'); ?>>
                                            <?php _e('End of every month', 'wployalty-point-email-reminder'); ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Year Options -->
                <div id="year_options" class="wlpr-frequency-options" style="display: <?php echo ($frequency_type === 'year') ? 'block' : 'none'; ?>;">
                    <div class="wlpr-data-content-row">
                        <label for="year_date" class="wlpr-data-content-label">
                            <?php _e('Yearly once at', 'wployalty-point-email-reminder'); ?>
                        </label>
                        <div class="wlpr-data-content-field">
                            <input type="date" id="year_date" name="year_date" 
                                   min="<?php echo date('Y-m-d'); ?>" onkeydown="return false;"
                                   value="<?php echo $is_edit ? esc_attr($rule_data['year_date'] ?? '') : ''; ?>">
                        </div>
                    </div>
                </div>

            </div>

            <!-- Include Banned User -->
            <div class="wlpr-data-content-row">
                <label class="wlpr-data-content-label">
                    <?php _e('Include banned user', 'wployalty-point-email-reminder'); ?>
                </label>
                <div class="wlpr-data-content-field">
                    <label>
                        <input type="checkbox" name="include_banned_users" value="1" 
                               <?php echo ($is_edit && !empty($rule_data['include_banned_users'])) ? 'checked' : ''; ?>>
                        <?php _e('Include banned users in the report', 'wployalty-point-email-reminder'); ?>
                    </label>
                </div>
            </div>

            <!-- Batch Limit -->
            <div class="wlpr-data-content-row">
                <label for="batch_limit" class="wlpr-data-content-label">
                    <?php _e('Batch Limit', 'wployalty-point-email-reminder'); ?>
                </label>
                <div class="wlpr-data-content-field">
                    <select id="batch_limit" name="batch_limit">
                        <?php
                        $current_batch_limit = $is_edit ? ($rule_data['batch_limit'] ?? 50) : 50;
                        $batch_options = [10, 20, 30, 40, 50, 60, 70, 80, 90, 100];
                        foreach($batch_options as $option):
                        ?>
                            <option value="<?php echo $option; ?>" <?php selected($current_batch_limit, $option); ?>>
                                <?php echo $option; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

        </div>
    </form>
</div> 
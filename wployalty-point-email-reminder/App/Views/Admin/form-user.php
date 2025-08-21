<?php
defined('ABSPATH') or die();

// Determine if this is edit mode
$is_edit = isset($rule_data) && !empty($rule_data);
$rule_id = $is_edit ? $rule_data['id'] : 0;
$form_title = $is_edit ? __('Edit User Report', 'wployalty-point-email-reminder') : __('Schedule User Report', 'wployalty-point-email-reminder');
$button_text = $is_edit ? __('Update', 'wployalty-point-email-reminder') : __('Save', 'wployalty-point-email-reminder');
$form_action = $is_edit ? 'wlpr_update_user_reminder' : 'wlpr_save_user_reminder';
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
            <button type="submit" form="wlpr-user-form" class="wlpr-button-action colored-button" id="wlpr-user-submit">
                <?php echo $button_text; ?>
            </button>
        </div>
    </div>

    <!-- Form Content -->
    <form id="wlpr-user-form" method="post">
        <input type="hidden" name="action" value="<?php echo $is_edit ? 'wlpr_update_reminder_rule' : 'wlpr_create_reminder_rule'; ?>">
        <input type="hidden" name="wlpr_nonce" value="<?php echo wp_create_nonce($is_edit ? 'wlpr_update_reminder_rule_nonce' : 'wlpr_create_reminder_rule_nonce'); ?>">
        <input type="hidden" name="category" value="user_report">
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
            <!-- Report Should Include Field (Accordion with Categories) -->
            <div class="wlpr-data-content-row">
                <div class="wlpr-report-accordion">
                    <div class="wlpr-accordion-header" id="user-report-accordion-header">
                        <label class="wlpr-data-content-label">
                            <?php _e('Report should include', 'wployalty-point-email-reminder'); ?>
                            <span class="wlpr-selected-count">(<?php echo $is_edit ? count($rule_data['report_includes'] ?? []) : '0'; ?> selected)</span>
                        </label>
                        <div class="wlpr-accordion-toggle">
                            <i class="dashicons dashicons-arrow-down-alt2"></i>
                        </div>
                    </div>
                    <div class="wlpr-accordion-content" id="user-report-accordion-content">
                        
                        <!-- Points Category -->
                        <div class="wlpr-category-section">
                            <div class="wlpr-category-header" data-category="points">
                                <h4><?php _e('Points', 'wployalty-point-email-reminder'); ?> <span class="wlpr-category-count">(0 selected)</span></h4>
                                <i class="dashicons dashicons-arrow-down-alt2"></i>
                            </div>
                            <div class="wlpr-category-content" id="points-content">
                                <div class="wlpr-report-options">
                                    <?php 
                                    $points_options = [
                                        'points_earned' => __('Earned X points in the last XY days', 'wployalty-point-email-reminder'),
                                        'points_redeemed' => __('Redeemed X points in the last XY days', 'wployalty-point-email-reminder'),
                                        'points_next_level' => __('X points away from reaching the next level', 'wployalty-point-email-reminder'),
                                        'points_expiring' => __('X points are going to expire in the upcoming XY days', 'wployalty-point-email-reminder')
                                    ];
                                    
                                    $selected_points = $is_edit ? ($rule_data['points_includes'] ?? []) : [];
                                    
                                    foreach ($points_options as $value => $label):
                                        $checked = in_array($value, $selected_points) ? 'checked' : '';
                                    ?>
                                        <label>
                                            <input type="checkbox" name="user_report_includes[]" value="<?php echo esc_attr($value); ?>" data-category="points" <?php echo $checked; ?>>
                                            <?php echo $label; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Rewards Category -->
                        <div class="wlpr-category-section">
                            <div class="wlpr-category-header" data-category="rewards">
                                <h4><?php _e('Rewards', 'wployalty-point-email-reminder'); ?> <span class="wlpr-category-count">(0 selected)</span></h4>
                                <i class="dashicons dashicons-arrow-down-alt2"></i>
                            </div>
                            <div class="wlpr-category-content" id="rewards-content">
                                <div class="wlpr-report-options">
                                    <?php 
                                    $rewards_options = [
                                        'rewards_earned' => __('Earned X rewards in the last XY days', 'wployalty-point-email-reminder'),
                                        'rewards_used' => __('Used X rewards in the last XY days', 'wployalty-point-email-reminder'),
                                        'rewards_expiring' => __('X rewards will expire in the upcoming XY days', 'wployalty-point-email-reminder')
                                    ];
                                    
                                    $selected_rewards = $is_edit ? ($rule_data['rewards_includes'] ?? []) : [];
                                    
                                    foreach ($rewards_options as $value => $label):
                                        $checked = in_array($value, $selected_rewards) ? 'checked' : '';
                                    ?>
                                        <label>
                                            <input type="checkbox" name="user_report_includes[]" value="<?php echo esc_attr($value); ?>" data-category="rewards" <?php echo $checked; ?>>
                                            <?php echo $label; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Levels Category -->
                        <div class="wlpr-category-section">
                            <div class="wlpr-category-header" data-category="levels">
                                <h4><?php _e('Levels', 'wployalty-point-email-reminder'); ?> <span class="wlpr-category-count">(0 selected)</span></h4>
                                <i class="dashicons dashicons-arrow-down-alt2"></i>
                            </div>
                            <div class="wlpr-category-content" id="levels-content">
                                <div class="wlpr-report-options">
                                    <?php 
                                    $levels_options = [
                                        'levels_progress' => __('X points away from reaching 1st next level, Y points away from reaching 2nd next level, and so on', 'wployalty-point-email-reminder'),
                                        'levels_benefits' => __('By reaching next level you get Y rewards (Achievement campaign benefits)', 'wployalty-point-email-reminder'),
                                        'levels_campaigns' => __('If you are next level user, you earn Campaigns configured with users next level', 'wployalty-point-email-reminder')
                                    ];
                                    
                                    $selected_levels = $is_edit ? ($rule_data['levels_includes'] ?? []) : [];
                                    
                                    foreach ($levels_options as $value => $label):
                                        $checked = in_array($value, $selected_levels) ? 'checked' : '';
                                    ?>
                                        <label>
                                            <input type="checkbox" name="user_report_includes[]" value="<?php echo esc_attr($value); ?>" data-category="levels" <?php echo $checked; ?>>
                                            <?php echo $label; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Is Recurring Field (Common) -->
            <div class="wlpr-data-content-row">
                <label class="wlpr-data-content-label">
                    <?php _e('Is recurring?', 'wployalty-point-email-reminder'); ?>
                </label>
                <div class="wlpr-data-content-field">
                    <label>
                        <input type="checkbox" id="user_is_recurring" name="is_recurring" value="1" 
                               <?php echo ($is_edit && !empty($rule_data['is_recurring'])) ? 'checked' : ''; ?>>
                        <?php _e('Enable recurring schedule', 'wployalty-point-email-reminder'); ?>
                    </label>
                </div>
            </div>

            <!-- One-time Date Picker (shown when not recurring) -->
            <div class="wlpr-data-content-row" id="user_one_time_date_row" style="display: <?php echo ($is_edit && empty($rule_data['is_recurring'])) ? 'block' : 'none'; ?>;">
                <label for="user_schedule_date" class="wlpr-data-content-label">
                    <?php _e('Select date', 'wployalty-point-email-reminder'); ?>
                </label>
                <div class="wlpr-data-content-field">
                    <input type="date" id="user_schedule_date" name="schedule_date" 
                           min="<?php echo date('Y-m-d'); ?>" onkeydown="return false;"
                           value="<?php echo $is_edit ? esc_attr($rule_data['schedule_date'] ?? '') : ''; ?>">
                </div>
            </div>

            <!-- Recurring Options (shown when recurring is checked) -->
            <div id="user_recurring_options" style="display: <?php echo ($is_edit && !empty($rule_data['is_recurring'])) ? 'block' : 'none'; ?>;">
                
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
                            <input type="radio" name="user_frequency_type" value="week" id="user_freq_week" 
                                   <?php echo ($frequency_type === 'week') ? 'checked' : ''; ?>>
                            <?php _e('Week', 'wployalty-point-email-reminder'); ?>
                        </label><br>
                        <label>
                            <input type="radio" name="user_frequency_type" value="month" id="user_freq_month" 
                                   <?php echo ($frequency_type === 'month') ? 'checked' : ''; ?>>
                            <?php _e('Month', 'wployalty-point-email-reminder'); ?>
                        </label><br>
                        <label>
                            <input type="radio" name="user_frequency_type" value="year" id="user_freq_year" 
                                   <?php echo ($frequency_type === 'year') ? 'checked' : ''; ?>>
                            <?php _e('Year', 'wployalty-point-email-reminder'); ?>
                        </label>
                    </div>
                </div>

                <!-- Week Options -->
                <div id="user_week_options" class="wlpr-frequency-options" style="display: <?php echo ($frequency_type === 'week') ? 'block' : 'none'; ?>;">
                    <div class="wlpr-data-content-row">
                        <div class="wlpr-row-filed">
                            <div>
                                <label for="user_week_every" class="wlpr-data-content-label">
                                    <?php _e('Every', 'wployalty-point-email-reminder'); ?>
                                </label>
                                <div class="wlpr-data-content-field">
                                    <select id="user_week_every" name="week_every">
                                        <?php 
                                        $week_every = $is_edit ? ($rule_data['week_every'] ?? 1) : 1;
                                        for ($i = 1; $i <= 4; $i++): 
                                        ?>
                                            <option value="<?php echo $i; ?>" <?php selected($week_every, $i); ?>>
                                                <?php echo sprintf(_n('%dst week', '%dnd week', '%drd week', '%dth week', $i, 'wployalty-point-email-reminder'), $i); ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label for="user_week_day" class="wlpr-data-content-label">
                                    <?php _e('Day', 'wployalty-point-email-reminder'); ?>
                                </label>
                                <div class="wlpr-data-content-field">
                                    <select id="user_week_day" name="week_day">
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
                <div id="user_month_options" class="wlpr-frequency-options" style="display: <?php echo ($frequency_type === 'month') ? 'block' : 'none'; ?>;">
                    <div class="wlpr-data-content-row">
                        <div class="wlpr-row-filed">
                            <div>
                                <label for="user_month_every" class="wlpr-data-content-label">
                                    <?php _e('Every', 'wployalty-point-email-reminder'); ?>
                                </label>
                                <div class="wlpr-data-content-field">
                                    <select id="user_month_every" name="month_every">
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
                                <label for="user_month_date" class="wlpr-data-content-label">
                                    <?php _e('Date', 'wployalty-point-email-reminder'); ?>
                                </label>
                                <div class="wlpr-data-content-field">
                                    <select id="user_month_date" name="month_date">
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
                <div id="user_year_options" class="wlpr-frequency-options" style="display: <?php echo ($frequency_type === 'year') ? 'block' : 'none'; ?>;">
                    <div class="wlpr-data-content-row">
                        <label for="user_year_date" class="wlpr-data-content-label">
                            <?php _e('Yearly once at', 'wployalty-point-email-reminder'); ?>
                        </label>
                        <div class="wlpr-data-content-field">
                            <input type="date" id="user_year_date" name="year_date" 
                                   min="<?php echo date('Y-m-d'); ?>" onkeydown="return false;"
                                   value="<?php echo $is_edit ? esc_attr($rule_data['year_date'] ?? '') : ''; ?>">
                        </div>
                    </div>
                </div>

            </div>

            <!-- Batch Limit (Common) -->
            <div class="wlpr-data-content-row">
                <label for="user_batch_limit" class="wlpr-data-content-label">
                    <?php _e('Batch Limit', 'wployalty-point-email-reminder'); ?>
                </label>
                <div class="wlpr-data-content-field">
                    <select id="user_batch_limit" name="batch_limit">
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
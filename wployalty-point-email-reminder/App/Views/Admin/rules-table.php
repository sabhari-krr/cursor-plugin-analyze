<?php
defined('ABSPATH') or die();

// Ensure we have the required variables
if (!isset($rules)) {
    $rules = [];
}
if (!isset($pagination)) {
    $pagination = [];
}
if (!isset($search_term)) {
    $search_term = '';
}
?>

<?php if (!empty($rules)): ?>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th class="check-column">
                    <input type="checkbox" id="wlpr-select-all" class="wlpr-select-all-checkbox">
                </th>
                <th><?php _e('Rule Name', 'wployalty-point-email-reminder'); ?></th>
                <th><?php _e('Type', 'wployalty-point-email-reminder'); ?></th>
                <th><?php _e('Schedule', 'wployalty-point-email-reminder'); ?></th>
                <th><?php _e('Status', 'wployalty-point-email-reminder'); ?></th>
                <th><?php _e('Last Sent', 'wployalty-point-email-reminder'); ?></th>
                <th><?php _e('Created', 'wployalty-point-email-reminder'); ?></th>
                <th><?php _e('Actions', 'wployalty-point-email-reminder'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rules as $rule): ?>
                <tr>
                    <td class="check-column">
                        <input type="checkbox" name="wlpr_rule_ids[]" value="<?php echo esc_attr($rule['id']); ?>" class="wlpr-rule-checkbox">
                    </td>
                    <td>
                        <div class="wlpr-rule-name">
                            <strong><?php echo !empty($rule['rule_name']) ? esc_html($rule['rule_name']) : __('Unnamed Rule', 'wployalty-point-email-reminder'); ?></strong>
                        </div>
                    </td>
                    <td>
                        <div class="wlpr-rule-type">
                            <span><?php echo ucfirst($rule['type']); ?>
                                <?php _e('Report', 'wployalty-point-email-reminder'); ?></span>
                        </div>
                    </td>
                    <td>
                        <div class="wlpr-rule-schedule">
                            <?php if (!empty($rule['is_recurring'])): ?>
                                <?php
                                $frequency = $rule['frequency_type'];
                                switch ($frequency) {
                                    case 'week':
                                        echo sprintf(
                                            __('Every %d week(s) on %s', 'wployalty-point-email-reminder'),
                                            $rule['week_every'],
                                            ucfirst($rule['week_day'])
                                        );
                                        break;
                                    case 'month':
                                        echo sprintf(
                                            __('Every %d month(s) on %s', 'wployalty-point-email-reminder'),
                                            $rule['month_every'],
                                            $rule['month_date'] === 'end' ? __('end of month', 'wployalty-point-email-reminder') : $rule['month_date']
                                        );
                                        break;
                                    case 'year':
                                        echo sprintf(
                                            __('Yearly on %s', 'wployalty-point-email-reminder'),
                                            date('M j', strtotime($rule['year_date']))
                                        );
                                        break;
                                }
                                ?>
                            <?php else: ?>
                                <?php echo date('M j, Y', strtotime($rule['schedule_date'])); ?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <span class="wlpr-status wlpr-status-<?php echo $rule['status']; ?>">
                            <?php
                            switch ($rule['status']) {
                                case 'active':
                                    _e('Active', 'wployalty-point-email-reminder');
                                    break;
                                case 'paused':
                                    _e('Paused', 'wployalty-point-email-reminder');
                                    break;
                                case 'completed':
                                    _e('Completed', 'wployalty-point-email-reminder');
                                    break;
                                default:
                                    _e('Unknown', 'wployalty-point-email-reminder');
                            }
                            ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        if (!empty($rule['last_sent'])) {
                            echo date('M j, Y g:i A', strtotime($rule['last_sent']));
                        } else {
                            echo '<span class="wlpr-no-data">' . __('Never', 'wployalty-point-email-reminder') . '</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <?php echo date('M j, Y', strtotime($rule['created_at'])); ?>
                    </td>
                    <td>
                        <div class="wlpr-rule-actions">
                            <?php
                            // Build edit URL with pagination and search parameters
                            $edit_args = [
                                'page' => 'wployalty-point-email-reminder',
                                'view' => 'edit',
                                'type' => $rule['type'],
                                'id' => $rule['id']
                            ];
                            
                            // Add current pagination and search parameters
                            if (isset($pagination)) {
                                if (!empty($pagination['current_page']) && $pagination['current_page'] > 1) {
                                    $edit_args['paged'] = $pagination['current_page'];
                                }
                                if (!empty($pagination['per_page'])) {
                                    $edit_args['per_page'] = $pagination['per_page'];
                                }
                            }
                            
                            // Add search term if it exists
                            if (!empty($search_term)) {
                                $edit_args['search'] = $search_term;
                            }
                            ?>
                            <a href="<?php echo admin_url('admin.php?' . http_build_query($edit_args)); ?>"
                                class="wlpr-action-edit"
                                title="<?php _e('Edit Rule', 'wployalty-point-email-reminder'); ?>">
                                <i class="dashicons dashicons-edit"></i>
                            </a>
                            <a href="#" class="wlpr-action-delete" data-rule-id="<?php echo $rule['id']; ?>"
                                title="<?php _e('Delete Rule', 'wployalty-point-email-reminder'); ?>">
                                <i class="dashicons dashicons-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="wlpr-no-results">
        <div class="wlpr-no-results-icon">
            <i class="dashicons dashicons-search"></i>
        </div>
        <div class="wlpr-no-results-title">
            <?php _e('No rules found', 'wployalty-point-email-reminder'); ?>
        </div>
        <div class="wlpr-no-results-message">
            <?php 
            if (!empty($search_term)) {
                printf(__('No rules match your search for "%s"', 'wployalty-point-email-reminder'), esc_html($search_term));
            } else {
                _e('No email reminder rules found.', 'wployalty-point-email-reminder');
            }
            ?>
        </div>
    </div>
<?php endif; ?> 
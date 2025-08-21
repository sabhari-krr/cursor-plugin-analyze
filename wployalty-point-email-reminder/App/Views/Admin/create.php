<?php
defined('ABSPATH') or die();
?>

<div class="wlpr-create-form-container">
    <!-- Form Header with Back button -->
    <div class="wlpr-form-header">
        <h2 class="wlpr-form-title"><?php _e('Create Email Reminder Rule', 'wployalty-point-email-reminder'); ?></h2>
        <div class="wlpr-form-actions">
            <a href="<?php echo admin_url('admin.php?page=wployalty-point-email-reminder&view=rules'); ?>" class="wlpr-button-action non-colored-button">
                <i class="dashicons dashicons-arrow-left-alt2"></i>
                <?php _e('Back to Rules', 'wployalty-point-email-reminder'); ?>
            </a>
        </div>
    </div>

    <!-- Selection Content -->
    <div class="wlpr-create-main">
        <div class="wlpr-create-cards-row">
            <!-- Admin Card -->
            <div class="wlpr-create-card">
                <div class="wlpr-create-card-icon">
                    <i class="dashicons dashicons-admin-users"></i>
                </div>
                <div class="wlpr-create-card-title"><?php _e('Admin Report', 'wployalty-point-email-reminder'); ?></div>
                <div class="wlpr-create-card-description">
                    <?php _e('Schedule automated reports to be sent to administrators with loyalty program insights and statistics.', 'wployalty-point-email-reminder'); ?>
                </div>
                <a href="<?php echo admin_url('admin.php?page=wployalty-point-email-reminder&view=create&type=admin'); ?>"
                    class="wlpr-button-action">
                    <?php _e('Create Admin Report', 'wployalty-point-email-reminder'); ?>
                </a>
            </div>

            <!-- User Card -->
            <div class="wlpr-create-card">
                <div class="wlpr-create-card-icon">
                    <i class="dashicons dashicons-groups"></i>
                </div>
                <div class="wlpr-create-card-title"><?php _e('User Report', 'wployalty-point-email-reminder'); ?></div>
                <div class="wlpr-create-card-description">
                    <?php _e('Schedule personalized reports to be sent to users about their points, rewards, and loyalty status.', 'wployalty-point-email-reminder'); ?>
                </div>
                <a href="<?php echo admin_url('admin.php?page=wployalty-point-email-reminder&view=create&type=user'); ?>"
                    class="wlpr-button-action">
                    <?php _e('Create User Report', 'wployalty-point-email-reminder'); ?>
                </a>
            </div>
        </div>
    </div>
</div>
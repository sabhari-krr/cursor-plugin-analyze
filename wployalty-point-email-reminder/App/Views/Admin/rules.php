<?php
defined('ABSPATH') or die();
?>

<div class="wlpr-rules-container">
    <!-- Rules Header with Create Button -->
            <div class="wlpr-rules-header">
            <h2><?php _e('Email Reminder Rules', 'wployalty-point-email-reminder'); ?></h2>
                    <div class="wlpr-rules-actions">
            <!-- Bulk Delete Button (hidden by default) -->
            <button type="button" id="wlpr-bulk-delete-btn" class="wlpr-button-action wlpr-bulk-delete-btn" style="display: none;">
                <i class="dashicons dashicons-trash"></i>
                <span id="wlpr-bulk-delete-text"><?php _e('Delete', 'wployalty-point-email-reminder'); ?></span>
            </button>
            
            <div class="wlpr-search-wrapper">
                <i class="dashicons dashicons-search"></i>
                <input type="text" id="wlpr-rule-search" 
                       placeholder="<?php _e('Search rules by name...', 'wployalty-point-email-reminder'); ?>"
                       class="wlpr-search-input">
                <button type="button" id="wlpr-clear-search" class="wlpr-clear-search" style="display: none;">
                    <i class="dashicons dashicons-no-alt"></i>
                </button>
            </div>
            <a href="<?php echo admin_url('admin.php?page=wployalty-point-email-reminder&view=create'); ?>"
                class="wlpr-button-action colored-button">
                <i class="dashicons dashicons-plus-alt2"></i>
                <?php _e('Create Rule', 'wployalty-point-email-reminder'); ?>
            </a>
        </div>
        </div>

    <!-- Rules List -->
    <div class="wlpr-rules-content">
        <?php if (empty($rules)): ?>
            <!-- No Rules State -->
            <div class="wlpr-empty-state">
                <div class="wlpr-empty-state-content">
                    <div class="wlpr-empty-state-icon">
                        <i class="dashicons dashicons-email-alt"></i>
                    </div>
                    <div class="wlpr-empty-state-text">
                        <h3><?php _e('No rules found', 'wployalty-point-email-reminder'); ?></h3>
                        <p><?php _e('Get started by creating your first email reminder rule.', 'wployalty-point-email-reminder'); ?></p>
                    </div>
                    <div class="wlpr-empty-state-action">
                        <a href="<?php echo admin_url('admin.php?page=wployalty-point-email-reminder&view=create'); ?>"
                            class="wlpr-button-action colored-button">
                            <i class="dashicons dashicons-plus-alt2"></i>
                            <?php _e('Create Rule', 'wployalty-point-email-reminder'); ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Rules Table -->
            <div class="wlpr-rules-table" id="wlpr-rules-table-container">
                <?php include(WLPR_VIEW_PATH . '/Admin/rules-table.php'); ?>
            </div>

            <!-- Pagination -->
            <?php if (!empty($pagination)): ?>
                <div class="wlpr-pagination">
                    <div class="wlpr-per-page-selector">
                        <label for="wlpr-per-page"><?php _e('Per page:', 'wployalty-point-email-reminder'); ?></label>

                        <select id="wlpr-per-page" class="wlpr-per-page-select">
                            <option value="5" <?php echo (intval($pagination['per_page']) === 5) ? 'selected' : ''; ?>>5</option>
                            <option value="10" <?php echo (intval($pagination['per_page']) === 10) ? 'selected' : ''; ?>>10
                            </option>
                            <option value="25" <?php echo (intval($pagination['per_page']) === 25) ? 'selected' : ''; ?>>25
                            </option>
                            <option value="50" <?php echo (intval($pagination['per_page']) === 50) ? 'selected' : ''; ?>>50
                            </option>
                            <option value="100" <?php echo (intval($pagination['per_page']) === 100) ? 'selected' : ''; ?>>100
                            </option>
                        </select>
                    </div>

                    <?php if ($pagination['total_pages'] > 1): ?>
                        <?php include(WLPR_VIEW_PATH . '/Admin/rules-pagination.php'); ?>
                        
                        <?php if ($pagination['total_pages'] > 10): ?>
                            <div class="wlpr-goto-page">
                                <label
                                    for="wlpr-goto-page-input"><?php _e('Go to page:', 'wployalty-point-email-reminder'); ?></label>
                                <input type="number" id="wlpr-goto-page-input" min="1"
                                    max="<?php echo $pagination['total_pages']; ?>"
                                    value="<?php echo $pagination['current_page']; ?>" class="wlpr-goto-page-input">
                                <button type="button" id="wlpr-goto-page-btn"
                                    class="wlpr-goto-page-button"><?php _e('Go', 'wployalty-point-email-reminder'); ?></button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
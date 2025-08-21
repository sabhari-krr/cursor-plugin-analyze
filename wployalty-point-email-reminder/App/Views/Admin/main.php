<?php

defined('ABSPATH') or die();

?>

<div id="wlpr-main-page">
    <div>
        <div class="wlpr-main-header">
            <h1><?php echo WLPR_PLUGIN_NAME; ?> </h1>
            <div><b><?php echo "v" . WLPR_PLUGIN_VERSION; ?></b></div>
        </div>
        <div class="wlpr-notice-header">
            <b><?php _e('Note', 'wployalty-point-email-reminder'); ?></b>: <?php _e('Configure email reminder rules to automatically send reports to administrators and users about their loyalty points and rewards.', 'wployalty-point-email-reminder'); ?>
        </div>
        <div class="wlpr-admin-main">
            <div class="wlpr-admin-nav">
                <a class="<?php echo (in_array($current_page, array(
                    'rules',
                    'create',
                    'edit',
                    'activity_details'
                ))) ? "active-nav" : ""; ?>" href="<?php echo admin_url("admin.php?" . http_build_query(array(
                         "page" => WLPR_PLUGIN_SLUG,
                         "view" => 'rules'
                     ))) ?>"><?php _e("Rules", "wployalty-point-email-reminder"); ?></a>
                <a class="<?php echo (in_array($current_page, array('settings'))) ? "active-nav" : ""; ?>" href="<?php echo admin_url("admin.php?" . http_build_query(array(
                            "page" => WLPR_PLUGIN_SLUG,
                            "view" => 'settings'
                        ))) ?>"><?php _e("Settings", "wployalty-point-email-reminder"); ?></a>
            </div>
        </div>
        <div class="wlpr-parent">
            <div class="wlpr-body-content">
                <?php if (isset($main_page) && !empty($main_page) && is_array($main_page)): ?>
                    <?php foreach ($main_page as $page): ?>
                        <?php echo $page; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div id="wlpr-overlay-section" class="wlpr-overlay-section">
        <div class="wlpr-overlay">
        </div>
    </div>
</div>
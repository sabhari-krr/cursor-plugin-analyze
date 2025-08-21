<?php
defined("ABSPATH") or exit;
?>

<div class="wrap">
    <div>
        <input type="hidden" name="_wp_post_mark_nonce" id="_wp_post_mark_nonce"
            value="<?php echo esc_attr(wp_create_nonce('auth_ajax')) ?>">
    </div>
    <div id="wp-relay-main">
        <!--        The Content will be rendered from react-->
    </div>
</div>

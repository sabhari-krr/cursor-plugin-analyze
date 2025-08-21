<?php
defined("ABSPATH") or exit;
?>

<div id="confirmation-block" class="<?php echo esc_attr($confirmation['container']) ?>">
    <?php
    if (!empty($confirmation['icon_url'])) { ?>
        <img class="<?php echo esc_attr($confirmation['icon_url_class']) ?>"
            src="<?php echo esc_html($confirmation['icon_url']) ?>" alt="Icon" />
    <?php } ?>

    <h3 class="<?php echo esc_attr($confirmation['header_text_class']) ?>"><?php echo esc_html($confirmation['header_text']) ?></h3>
    <p class="<?php echo esc_attr($confirmation['body_text_class']) ?>">
        <?php echo wp_kses_post($confirmation['body_text']) ?>
    </p>
</div>

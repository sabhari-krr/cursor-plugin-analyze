<?php
defined("ABSPATH") or exit;
?>

<select name="rwp_order">
    <option
        value="<?php echo esc_attr("wp_relay_orders") ?>"
        <?php echo $selected == esc_attr("wp_relay_orders") ? esc_attr("selected") : "" ?>>
        <?php echo esc_attr(RWPA_PLUGIN_NAME) ?> Orders
    </option>
    <option
        value="<?php echo esc_attr("recurring") ?>"
        <?php echo $selected == esc_attr("recurring") ? esc_attr("selected") : "" ?>>
        <?php echo esc_attr__("Recurring Orders Only", "relay-affiliate-marketing") ?>
    </option>
    <option
        value="all"
        <?php echo $selected == esc_attr("all") ? esc_attr("selected") : "" ?>>
        <?php echo esc_attr__("All Orders", "relay-affiliate-marketing") ?>
    </option>
</select>

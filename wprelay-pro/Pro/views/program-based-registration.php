<?php
defined("ABSPATH") or exit;
?>


<style data-wprelay>
    <?php
    if ($rendering_details['advanced_css']['enabled']) { ?><?php echo wp_kses_post($rendering_details['advanced_css']['styles']) ?><?php } ?>
</style>
<?php
$fields_container = $rendering_details['fields_container'];
?>
<div id="wprelay-pbar-block" class="<?php echo esc_attr($rendering_details['content']['wrapper']) ?>">
    <div class="<?php echo esc_attr($rendering_details['content']['container']) ?>">
        <!--    overview section-->
        <div class="<?php echo esc_attr($rendering_details['overview']['container']) ?>">
            <h3 class="<?php echo esc_attr($rendering_details['overview']['overview_title_class']) ?>"><?php echo esc_html($rendering_details['overview']['title']) ?></h3>
            <p class="<?php echo esc_attr($rendering_details['overview']['overview_description_class']) ?>">
                <?php
                echo wp_kses_post($rendering_details['overview']['description'])
                ?>
            </p>
        </div>

        <!--    form Fields-->
        <form action="" method="POST" id="wprelay-pbar-form"
            class="<?php echo esc_attr($fields_container['fields_wrapper']) ?>">
            <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
            <input type="hidden" name="_wp_nonce_key" value="<?php echo esc_attr($nonce['_wp_nonce_key']) ?>">
            <input type="hidden" name="_wp_nonce" value="<?php echo esc_attr($nonce['_wp_nonce']) ?>">
            <input type="hidden" name="action" value="<?php echo esc_attr($actionName) ?>" id="wp_relay_action_name">
            <input type="hidden" name="program_id" value="<?php echo esc_attr($program->id) ?>" id="program_id">

            <div class="<?php echo esc_attr($fields_container['input_container_class']) ?>">
                <label class="<?php echo esc_attr($fields_container['label_class']) ?>"
                    for=""><?php echo esc_html__('First Name', 'relay-affiliate-marketing') ?> <span
                        class="wprelay-mandatory-field">*</span></label>
                <input class="<?php echo esc_attr($fields_container['input_class']) ?> input-text " name="first_name"
                    id="aff_first_name"
                    type="text" placeholder="John" value="<?php echo esc_attr($firstName) ?>">
                <p class="wprelay-text-danger"></p>
            </div>
            <div class="<?php echo esc_attr($fields_container['input_container_class']) ?>">
                <label class="<?php echo esc_attr($fields_container['label_class']) ?>"
                    for=""><?php echo esc_html__('Last Name', 'relay-affiliate-marketing') ?> <span
                        class="wprelay-mandatory-field">*</span></label>
                <input class="<?php echo esc_attr($fields_container['input_class']) ?> input-text " id="aff_last_name"
                    name="last_name"
                    type="text"
                    placeholder="Doe" value="<?php echo esc_attr($lastName) ?>">
                <p class="wprelay-text-danger"></p>
            </div>

            <div class="<?php echo esc_attr($fields_container['input_container_class']) ?>">
                <label class="<?php echo esc_attr($fields_container['label_class']) ?>"> <?php echo esc_html__('Email', 'relay-affiliate-marketing') ?>
                    <span
                        class="wprelay-mandatory-field">*</span></label>
                <input class="<?php echo esc_attr($fields_container['input_class']) ?> input-text " type="email"
                    id="aff_email"
                    name="email"
                    placeholder="johndoe@example.com" value="<?php echo esc_attr($email) ?>">
                <p class="wprelay-text-danger"></p>
            </div>

            <?php foreach ($rendering_details['fields'] as $field) { ?>
                <!--                    text/number fields -->
                <div class="<?php echo esc_attr($fields_container['input_container_class']) ?>">
                    <label class="<?php echo esc_attr($field['label_class']) ?>"><?php echo esc_html($field['label']) ?>
                        <?php
                        if ($field['is_important']) { ?>
                            <span class="wprelay-mandatory-field">*</span>
                        <?php
                        }
                        ?></label>
                    <input class="wprelay-input input-text  <?php echo esc_attr($field['input_class']) ?>"
                        name="<?php echo esc_attr($field['name']) ?>"
                        id="aff_<?php echo esc_attr($field['name']) ?>"
                        type="<?php echo esc_attr($field['type']) ?>"
                        placeholder="<?php echo esc_attr($field['label'] ?? '') ?>" />
                    <p class="wprelay-text-danger"></p>
                </div>

            <?php } ?>
        </form>

        <!--    //Submit Button-->
        <div class="<?php echo esc_attr($rendering_details['overview']['button_container_class']) ?> wprelay-each-section">
            <button type="submit" id="wprelay-pbaf-reg-btn"
                class="<?php echo esc_attr($rendering_details['overview']['button_class']) ?> wp-element-button wp-block-button__link">
                <span><?php echo esc_html($rendering_details['overview']['submit_text']) ?></span>
            </button>
        </div>
    </div>

    <!--    //Confirmation Block will be injected here on success response through ajax-->
    <!--   with  id="confirmation-block" div -->

</div>

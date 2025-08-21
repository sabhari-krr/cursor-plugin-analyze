<?php
defined("ABSPATH") or exit;
?>

<div id="wprelay-registration-block">
    <form action="" method="POST" id="wprelay-registration-form">
        <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
        <div class="wprelay-settings-tabcontent">
            <div class="wprelay-personal-info-section  wprelay-each-section">
                <input type="hidden" name="_wp_nonce_key" value="<?php echo esc_attr($nonce['_wp_nonce_key']) ?>">
                <input type="hidden" name="_wp_nonce" value="<?php echo esc_attr($nonce['_wp_nonce']) ?>">
                <input type="hidden" name="action" value="<?php echo esc_attr($actionName) ?>"
                    id="wp_relay_action_name">

                <div class="wprelay-two-columns">
                    <div class="wprelay-field-section">
                        <label class="wprelay-text-color" class="wprelay-text-color"
                            for="aff_first_name"><?php echo esc_attr__('First Name', 'relay-affiliate-marketing') ?><span
                                class="wprelay-mandatory-field">*</span></label>
                        <input class="wprelay-input input-text affiliate-field" name="first_name"
                            id="aff_first_name"
                            type="text" placeholder="John" value="<?php echo esc_attr($firstName) ?>">
                        <p class="wprelay-text-danger"></p>
                    </div>
                    <div class="wprelay-field-section">
                        <label class="wprelay-text-color"
                            for="aff_last_name"><?php echo esc_attr__('Last Name', 'relay-affiliate-marketing') ?><span
                                class="wprelay-mandatory-field">*</span></label>
                        <input class="wprelay-input input-text affiliate-field" id="aff_last_name"
                            name="last_name"
                            type="text"
                            placeholder="Doe" value="<?php echo esc_attr($lastName) ?>">
                        <p class="wprelay-text-danger"></p>
                    </div>
                </div>
                <div class="wprelay-two-columns">
                    <div class="wprelay-field-section">
                        <label for="aff_email"> <?php echo esc_attr__('Email', 'relay-affiliate-marketing') ?> <span
                                class="wprelay-mandatory-field">*</span></label>
                        <input class="wprelay-input input-text affiliate-field" type="email" id="aff_email"
                            name="email"
                            placeholder="johndoe@example.com" value="<?php echo esc_attr($email) ?>">
                        <p class="wprelay-text-danger"></p>
                    </div>

                    <div class="wprelay-field-section">
                        <label for="aff_phone_number"><?php echo esc_attr__('Phone Number', 'relay-affiliate-marketing') ?> <span
                                class="wprelay-mandatory-field">*</span></label>
                        <input class="wprelay-input input-text affiliate-field" name="phone_number"
                            id="aff_phone_number"
                            type="text" placeholder="9876543210">
                        <p class="wprelay-text-danger"></p>
                    </div>
                </div>
            </div>

            <div class="wprelay-shipping-info-section wprelay-each-section">
                <div class="wprelay-address-section wprelay-field-section">
                    <label for="aff_address"><?php echo esc_attr__('Address', 'relay-affiliate-marketing') ?> <span
                            class="wprelay-mandatory-field">*</span></label>
                    <input class="wprelay-input input-text affiliate-field" name="address" id="aff_address"
                        type="text"
                        placeholder=<?php echo esc_attr__('Address', 'relay-affiliate-marketing') ?>>
                    <p class="wprelay-text-danger"></p>
                </div>

                <div class="wprelay-two-columns">
                    <div class="wprelay-field-section">
                        <label for="aff_city"><?php echo esc_attr__('City', 'relay-affiliate-marketing') ?> <span
                                class="wprelay-mandatory-field">*</span></label>
                        <input class="wprelay-input input-text affiliate-field" name="city" id="aff_city"
                            type="text"
                            placeholder=<?php echo esc_attr__('City', 'relay-affiliate-marketing') ?>>
                        <p class="wprelay-text-danger"></p>
                    </div>

                    <div class="wprelay-field-section">
                        <label for="aff_zip_code"><?php echo esc_attr__('Zip Code', 'relay-affiliate-marketing') ?> <span
                                class="wprelay-mandatory-field">*</span></label>
                        <input class="wprelay-input input-text affiliate-field" id="aff_zip_code"
                            name="zip_code"
                            type="text"
                            placeholder=<?php echo esc_attr__('Zip Code', 'relay-affiliate-marketing') ?>>
                        <p class="wprelay-text-danger"></p>
                    </div>
                </div>

                <div class="wprelay-two-columns">
                    <div class="wprelay-country-section wprelay-field-section">
                        <label for="aff_country"><?php echo esc_attr__('Country', 'relay-affiliate-marketing') ?> <span
                                class="wprelay-mandatory-field">*</span></label>
                        <select class="affiliate-field" name="country" id="aff_country">
                            <option value="">Select Option</option>
                            <?php foreach ($countries as $value => $label) { ?>
                                <option value="<?php echo esc_attr($value) ?>" <?php echo $default_country_code == $value ? 'selected' : '' ?>><?php echo esc_html($label) ?></option>
                            <?php } ?>
                        </select>
                        <p class="wprelay-text-danger"></p>
                    </div>


                    <div class="wprelay-field-section">
                        <label for="aff_state"><?php echo esc_attr__('State', 'relay-affiliate-marketing') ?></label>
                        <select class="affiliate-field" name="state" id="aff_state">
                            <option value="">Select Option</option>
                            <?php foreach (\RelayWp\Affiliate\App\Helpers\WC::getStates($default_country_code ?? '') ?? [] as $value => $label) { ?>
                                <option value="<?php echo esc_attr($value) ?>">
                                    <?php echo esc_html($label) ?>
                                </option>
                            <?php } ?>
                        </select>
                        <p class="wprelay-text-danger"></p>
                    </div>
                </div>
            </div>

            <div class="wprelay-social-links-section wprelay-each-section">
                <div>
                    <span class="wprelay-text-color"><?php echo esc_attr__('Social Links', 'relay-affiliate-marketing') ?></span>
                </div>

                <div class="wprelay-two-columns">
                    <div class="wprelay-field-section">
                        <label for="aff_facebook_url"><?php echo esc_attr__('Facebook', 'relay-affiliate-marketing') ?></label>
                        <input class="wprelay-input input-text affiliate-field" id="aff_facebook_url"
                            name="facebook_url"
                            type="url" placeholder="https://www.facebook.com/">
                        <p class="wprelay-text-danger"></p>
                    </div>

                    <div class="wprelay-field-section">
                        <label for="aff_youtube_url"><?php echo esc_attr__('Youtube', 'relay-affiliate-marketing') ?></label>
                        <input class="wprelay-input input-text affiliate-field" id="aff_youtube_url"
                            name="youtube_url"
                            type="url" placeholder="https://www.youtube.com/">
                        <p class="wprelay-text-danger"></p>
                    </div>
                </div>


                <div class="wprelay-two-columns">
                    <div class="wprelay-field-section">
                        <label class="wprelay-text-color"
                            for="aff_tiktok_url"><?php echo esc_attr__('Tiktok', 'relay-affiliate-marketing') ?></label>
                        <input class="wprelay-input input-text affiliate-field" id="aff_tiktok_url"
                            name="tiktok_url"
                            type="url" placeholder="https://www.johndoe.com/">
                        <p class="wprelay-text-danger"></p>
                    </div>

                    <div class="wprelay-field-section">
                        <label class="wprelay-text-color"
                            for="aff_twitter_url"><?php echo esc_attr__('Twitter', 'relay-affiliate-marketing') ?></label>
                        <input class="wprelay-input input-text affiliate-field" id="aff_twitter_url"
                            name="twitter_url"
                            type="url" placeholder="https://www.twitter.com/">
                        <p class="wprelay-text-danger"></p>
                    </div>
                </div>

                <div class="wprelay-two-columns">
                    <div class="wprelay-field-section">
                        <label class="wprelay-text-color"
                            for="aff_instagram_url"><?php echo esc_attr__('Instagram', 'relay-affiliate-marketing') ?></label>
                        <input class="wprelay-input input-text affiliate-field" id="aff_instagram_url"
                            name="instagram_url"
                            type="url" placeholder="https://www.instagram.com/">
                        <p class="wprelay-text-danger"></p>
                    </div>

                    <div class="wprelay-field-section">
                        <label class="wprelay-text-color"
                            for="aff_website_url"><?php echo esc_attr__('Website', 'relay-affiliate-marketing') ?></label>
                        <input class="wprelay-input input-text affiliate-field" id="aff_website_url"
                            name="website_url"
                            type="url" placeholder="https://www.johndoe.com/">
                        <p class="wprelay-text-danger"></p>
                    </div>
                </div>
                <div class="wprelay-linkedin-section wprelay-field-section">
                    <label for="linkedin_url"><?php echo esc_attr__('Linkedin', 'relay-affiliate-marketing') ?></label>
                    <input class="wprelay-input input-text affiliate-field" name="linkedin_url"
                        id="aff_linkedin_url"
                        type="url" placeholder="https://www.linkedin.com/in/johndoe/">
                    <p class="wprelay-text-danger"></p>
                </div>

                <?php
                do_action('rwpa_wprelay-store-front-custom-fields');
                ?>
            </div>

            <div class="wprelay-save-button wprelay-each-section">
                <button class="wprelay-reg-btn wp-element-button wp-block-button__link" type="submit"
                    id="wprelay-reg-btn">
                    <span><?php echo esc_attr__('Register', 'relay-affiliate-marketing') ?></span>
                </button>
            </div>
        </div>
    </form>
    <div id="confirmation-block" class="hide">
            <?php echo wp_kses_post($affiliate_success_message); ?>
    </div>
</div>

<?php
defined("ABSPATH") or exit;
?>

<div class="wprelay-main-settings">
    <div id="wprelay-settings" class="wprelay-tabcontent">
        <form action="" id="wprelay-detail-form">
            <div id="wprelay-settings-profiles" class="wprelay-settings-tabcontents">

                <input type="hidden" name="affiliate_id" value="<?php echo esc_attr($affiliate->id) ?>">
                <input type="hidden" name="method" value="store_front_update_affiliate">
                <input type="hidden" name="_wp_nonce_key" value="menu_nonce">
                <input type="hidden" name="_wp_nonce" value="<?php echo esc_attr($menu_nonce) ?>">

                <div class="wprelay-personal-info-section">
                    <div class="personal-info-header">
                        <i class="rwp rwp-user"></i>
                        <span><?php echo esc_html__('Personal Info', 'relay-affiliate-marketing') ?></span>
                    </div>
                    <div class="wprelay-row-wrap">
                        <div>
                            <label for="aff_first_name"><?php echo esc_html__('First Name', 'relay-affiliate-marketing') ?> <span
                                    class="wprelay-mandatory-field">*</span> </label>
                            <input type="text" id="aff_first_name" name="first_name" value="<?php echo esc_attr($member->first_name) ?>"
                                placeholder="John" readonly>
                            <p class="wprelay-text-danger"></p>
                        </div>
                        <div><label for="aff_last_name"><?php echo esc_html__('Last Name', 'relay-affiliate-marketing') ?> <span
                                    class="wprelay-mandatory-field">*</span></label>
                            <input type="text" id="aff_last_name" name="last_name" value="<?php echo esc_attr($member->last_name) ?>"
                                placeholder="Doe" readonly>
                            <p class="wprelay-text-danger"></p>
                        </div>
                    </div>
                    <div class="wprelay-row-wrap">
                        <div class="wprelay-email-section">
                            <label for="email"> <?php echo esc_html__('Email', 'relay-affiliate-marketing') ?> <span class="wprelay-mandatory-field">*</span></label>
                            <input type="text" id="email" name="email" value="<?php echo esc_attr($member->email) ?>"
                                placeholder="johndoe@example.com" readonly>
                            <p class="wprelay-text-danger"></p>
                        </div>

                        <div>
                            <label for="aff_phone_number"> <?php echo esc_html__('Phone Number', 'relay-affiliate-marketing') ?> <span
                                    class="wprelay-mandatory-field">*</span></label>
                            <input type="text" id="aff_phone_number" name="phone_number"
                                value="<?php echo esc_attr($affiliate->phone_number) ?>" placeholder="9876543210">
                            <p class="wprelay-text-danger"></p>
                        </div>
                    </div>
                    <div class="wprelay-single-row">
                        <label for="aff_billing_email"><?php echo esc_html__('Billing Email', 'relay-affiliate-marketing') ?> </label>
                        <input type="text" id="aff_billing_email" name="billing_email"
                            value="<?php echo esc_attr($affiliate->payment_email) ?>"
                            placeholder="johndoe@example.com">
                        <p class="wprelay-text-danger"></p>
                    </div>

                </div>

                <?php
                $shipping_address = json_decode($affiliate->shipping_address, true);
                ?>
                <div class="wprelay-shipping-info-section">
                    <div class="shipping-info-header">
                        <i class="rwp rwp-truck"></i>
                        <span><?php echo esc_html__('Shipping Address', 'relay-affiliate-marketing') ?></span>
                    </div>
                    <div class="wprelay-row-wrap">
                        <div>
                            <label for="aff_address"><?php echo esc_html__('Address', 'relay-affiliate-marketing') ?> <span class="wprelay-mandatory-field">*</span></label>
                            <input type="text" id="aff_address" name="address"
                                value="<?php echo esc_attr($shipping_address['address'] ?? '') ?>"
                                placeholder="Address">
                            <p class="wprelay-text-danger"></p>
                        </div>

                        <div>
                            <label for="aff_city"><?php echo esc_html__('City', 'relay-affiliate-marketing') ?> <span
                                    class="wprelay-mandatory-field">*</span></label>
                            <input type="text" id="aff_city" name="city" value="<?php echo esc_attr($shipping_address['city'] ?? '') ?>"
                                placeholder="City">
                            <p class="wprelay-text-danger"></p>
                        </div>
                    </div>
                    <div class="wprelay-row-wrap">
                        <div>
                            <label for="aff_zip_code"><?php echo esc_html__('Zip Code', 'relay-affiliate-marketing') ?> <span
                                    class="wprelay-mandatory-field">*</span></label>
                            <input type="text" id="aff_zip_code" name="zip_code"
                                value="<?php echo esc_attr($shipping_address['zip_code'] ?? '') ?>" placeholder="ZIP-CODE">
                            <p class="wprelay-text-danger"></p>
                        </div>
                        <div>
                            <label for="aff_country"><?php echo esc_html__('Country', 'relay-affiliate-marketing') ?> <span class="wprelay-mandatory-field">*</span></label>
                            <select class="affiliate-field" name="country" id="aff_country">
                                <option value=""><?php echo esc_html__('Select Option', 'relay-affiliate-marketing') ?></option>
                                <?php foreach ($countries ?? [] as $value => $label) { ?>
                                    <option value="<?php echo esc_attr($value) ?>" <?php echo (($shipping_address['country'] ?? '') == $value ? 'selected' : '') ?>>
                                        <?php echo esc_html($label) ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <p class="wprelay-text-danger"></p>
                        </div>
                    </div>
                    <div class="wprelay-single-row">
                        <label for="aff_state"><?php echo esc_html__('State', 'relay-affiliate-marketing') ?></label>
                        <select class="affiliate-field" name="state" id="aff_state">
                            <option value=""><?php echo esc_html__('Select Option', 'relay-affiliate-marketing') ?></option>
                            <?php foreach (\RelayWp\Affiliate\App\Helpers\WC::getStates($shipping_address['country'] ?? '') ?? [] as $value => $label) { ?>
                                <option value="<?php echo esc_attr($value) ?>" <?php echo (($shipping_address['state'] ?? '') == $value ? 'selected' : '') ?>>
                                    <?php echo esc_html($label) ?>
                                </option>
                            <?php } ?>
                        </select>
                        <!--                        <input type="text" id="aff_state" value="-->
                        <?php //= $shipping_address['state'] ?? '' 
                        ?><!--" name="state" placeholder="STATE">-->
                        <p class="wprelay-text-danger"></p>
                    </div>
                </div>
                <?php
                $social_links = json_decode($affiliate->social_links, true);
                ?>
                <div class="wprelay-social-links-section">
                    <div class="social-links-header">
                        <i class="rwp rwp-social"></i>
                        <span><?php echo esc_html__('Social Links', 'relay-affiliate-marketing') ?></span>
                    </div>
                    <div class="wprelay-row-wrap">
                        <div>
                            <label for="aff_facebook_url"><?php echo esc_html__('Facebook', 'relay-affiliate-marketing') ?></label>
                            <input type="url" id="aff_facebook_url" name="facebook_url"
                                value="<?php echo esc_url($social_links['facebook_url'] ?? '') ?>"
                                placeholder="https://www.facebook.com/" />
                            <p class="wprelay-text-danger"></p>
                        </div>

                        <div>
                            <label for="aff_youtube_url"><?php echo esc_html__('Youtube', 'relay-affiliate-marketing') ?></label>
                            <input type="url" id="aff_youtube_url" name="youtube_url"
                                value="<?php echo esc_url($social_links['youtube_url'] ?? '') ?>"
                                placeholder="https://www.youtube.com/" />
                            <p class="wprelay-text-danger"></p>
                        </div>
                    </div>
                    <div class="wprelay-row-wrap">
                        <div>
                            <label for="aff_twitter_url"><?php echo esc_html__('Twitter', 'relay-affiliate-marketing') ?></label>
                            <input type="url" id="aff_twitter_url" name="twitter_url"
                                value="<?php echo esc_url($social_links['twitter_url'] ?? '') ?>"
                                placeholder="https://www.twitter.com/" />
                            <p class="wprelay-text-danger"></p>
                        </div>
                        <div>
                            <label for="aff_instagram_url"><?php echo esc_html__('Instagram', 'relay-affiliate-marketing') ?></label>
                            <input type="url" id="aff_instagram_url" name="instagram_url"
                                value="<?php echo esc_url($social_links['instagram_url'] ?? '') ?>"
                                placeholder="https://www.instagram.com/" />
                            <p class="wprelay-text-danger"></p>
                        </div>
                    </div>
                    <div class="wprelay-row-wrap">
                        <div>
                            <label for="aff_tiktok_url"><?php echo esc_html__('Tiktok', 'relay-affiliate-marketing') ?></label>
                            <input type="url" id="aff_tiktok_url" name="tiktok_url"
                                value="<?php echo esc_url($social_links['tiktok_url'] ?? '') ?>"
                                placeholder="https://www.johndoe.com/" />
                            <p class="wprelay-text-danger"></p>
                        </div>

                        <div>
                            <label for="aff_website_url"><?php echo esc_html__('Website', 'relay-affiliate-marketing') ?></label>
                            <input type="url" name="website_url" id="aff_website_url"
                                value="<?php echo esc_url($social_links['website_url'] ?? '') ?>"
                                placeholder="https://www.johndoe.com/">
                            <p class="wprelay-text-danger"></p>
                        </div>
                    </div>
                    <div class="wprelay-single-row">
                        <label for="aff_linkedin_url"><?php echo esc_html__('Linkedin', 'relay-affiliate-marketing') ?></label>
                        <input type="url" name="linkedin_url" id="aff_linkedin_url"
                            value="<?php echo esc_url($social_links['linkedin_url'] ?? '') ?>"
                            placeholder="https://www.linkedin.com/" />
                        <p class="wprelay-text-danger"></p>
                    </div>
                </div>
                <?php
                do_action('rwpa_wprelay-store-front-custom-fields');
                ?>
            </div>
            <div class="wprelay-save-button">
                <button class="button button-primary" id="wprelay-save-changes-btn" type="submit">
                    <span><?php echo esc_html__('Save Changes', 'relay-affiliate-marketing') ?></span>
                </button>
            </div>
        </form>
    </div>

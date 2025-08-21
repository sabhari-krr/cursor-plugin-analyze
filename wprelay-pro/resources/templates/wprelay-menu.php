<?php
defined("ABSPATH") or exit;
?>
<div>
    <div class="wprelay-section-1">
        <div class="wprelay-header-section">
            <div class="wprelay-header-right-main">
                <svg width="100" height="100" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="7" r="5" stroke="black" stroke-width="1.5" fill="#E0E0E0" />
                    <path d="M4 20.5C4 17.4624 7.13401 15 12 15C16.866 15 20 17.4624 20 20.5" stroke="black" stroke-width="1.5" fill="#E0E0E0" />
                </svg>
                <div class="wprelay-header-rightside">
                    <div class="wprelay-email-currency-section">
                        <p>
                            <?php echo esc_attr($member->email) ?>
                        </p>
                        <div class="currencies-list">
                            <select name="currency" id="affiliate-multi-currency-dropdown">
                                <?php foreach ($currencies as $value => $label) { ?>
                                    <option value="<?php echo esc_attr($value) ?>" <?php echo esc_attr($selected_currency == $value ? 'selected' : '') ?>> <?php echo esc_html($value) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="wprelay-code">
                        <p href="" id="wprelay-referral-link">
                            <?php echo esc_url(\RelayWp\Affiliate\Core\Models\Affiliate::getReferralCodeURL($affiliate)) ?>
                            <span id="wprelay-referral-link-copy"><i class="rwp rwp-copy"></i></span>
                        </p>

                    </div>
                </div>
            </div>
        </div>
        <div class="wprelay-tabs-section">
            <?php if ($affiliate->status == 'approved') { ?>
                <a class="wprelay-tablinks <?php echo esc_attr($current_active_section == 'overview' ? 'active' : ''); ?>"
                    style="<?php echo esc_attr($current_active_section == 'overview' ? 'background-color: ' .  $secondaryColor. ';border-radius: 6px;' : '') ?>"
                    href="<?php echo esc_url(add_query_arg(['section' => 'overview', 'current_page' => false, 'per_page' => false], $current_url)) ?>">
                    <i class="rwp rwp-graph"></i>
                    <span
                            style="<?php echo esc_attr($current_active_section == 'overview' ? 'color: ' .  $primaryColor : '') ?>"
                    >
                        <?php echo esc_html__('Overview', 'relay-affiliate-marketing') ?>
                    </span>
                </a>
                <a class="wprelay-tablinks <?php echo esc_attr($current_active_section == 'sales' ? 'active' : ''); ?>"
                   style="<?php echo esc_attr($current_active_section == 'sales' ? 'background-color: ' .  $secondaryColor. ';border-radius: 6px;' : '') ?>"
                   href="<?php echo esc_url(add_query_arg(['section' => 'sales', 'current_page' => 1], $current_url)) ?>">
                    <i class="rwp rwp-sales"></i>
                    <span
                            style="<?php echo esc_attr($current_active_section == 'sales' ? 'color: ' .  $primaryColor : '') ?>"
                    >
                        <?php echo esc_html__('Sales', 'relay-affiliate-marketing') ?>
                    </span>
                </a>
                <a class="wprelay-tablinks <?php echo esc_attr($current_active_section == 'commissions' ? 'active' : ''); ?>"
                   style="<?php echo esc_attr($current_active_section == 'commissions' ? 'background-color: ' .  $secondaryColor. ';border-radius: 6px;' : '') ?>"
                   href="<?php echo esc_url(add_query_arg(['section' => 'commissions', 'current_page' => 1], $current_url)) ?>">
                    <i class="rwp rwp-commissions"></i>
                    <span
                            style="<?php echo esc_attr($current_active_section == 'commissions' ? 'color: ' .  $primaryColor : '') ?>"
                    >
                        <?php echo esc_html__('Commissions', 'relay-affiliate-marketing') ?>
                    </span>
                </a>
                <a class="wprelay-tablinks <?php echo esc_attr($current_active_section == 'payouts' ? 'active' : ''); ?>"
                   style="<?php echo esc_attr($current_active_section == 'payouts' ? 'background-color: ' .  $secondaryColor. ';border-radius: 6px;' : '') ?>"
                   href="<?php echo esc_url(add_query_arg(['section' => 'payouts', 'current_page' => 1], $current_url)) ?>">
                    <i class="rwp rwp-payouts"></i>
                    <span
                            style="<?php echo esc_attr($current_active_section == 'payouts' ? ('color: ' .  $primaryColor) : '') ?>"
                    >
                        <?php echo esc_html__('Payouts', 'relay-affiliate-marketing') ?>
                    </span>
                </a>
            <?php } ?>
            <a class="wprelay-tablinks <?php echo esc_attr($current_active_section == 'settings' ? 'active' : ''); ?>"
               style="<?php echo esc_attr($current_active_section == 'settings' ? 'background-color: ' .  $secondaryColor. ';border-radius: 6px;' : '') ?>"
               href="<?php echo esc_url(add_query_arg(['section' => 'settings', 'current_page' => false, 'per_page' => false], $current_url)) ?>">
                <i class="rwp rwp-settings"></i>
                <span
                        style="<?php echo esc_attr($current_active_section == 'settings' ? ('color: ' .  $primaryColor) : '') ?>"
                >
                    <?php echo esc_html__('Settings', 'relay-affiliate-marketing') ?>
                </span>
            </a>
        </div>
    </div>
    <div>
        <?php wc_get_template($file_name,$inside,RWPA_TEMPLATE_OVERRIDE_DIR_PATH,$file_path) ?>
    </div>
</div>



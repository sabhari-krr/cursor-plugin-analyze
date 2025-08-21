<?php
defined("ABSPATH") or exit;
?>

<div>
    <div class="wprelay-section-payouts">
        <div class="payouts-header">
            <div class='payouts-main-header'>
                <i class="rwp rwp-payouts"></i>
                <h3><?php echo esc_html__('Payouts', 'relay-affiliate-marketing') ?></h3>
            </div>
        </div>
        <?php if (empty($data['payouts'])) { ?>
            <div class="wprelay-no-payouts">
                <h5><?php echo esc_html__('No Money Out Yet', 'relay-affiliate-marketing') ?></h5>
                <p><?php echo esc_html__("Oops, looks like there ain't no money to be paid out h ere! Better start promoting to fill up this empty state!", 'relay-affiliate-marketing') ?></p>
            </div>
        <?php } else { ?>
            <div class="payouts-table">
                <table>
                    <thead>
                        <tr>
                            <th scope="col"><?php echo esc_html__('Date', 'relay-affiliate-marketing') ?></th>
                            <th scope="col"><?php echo esc_html__('Amount', 'relay-affiliate-marketing') ?></th>
                            <th scope="col"><?php echo esc_html__('Status', 'relay-affiliate-marketing') ?></th>
                            <th scope="col"><?php echo esc_html__('Source', 'relay-affiliate-marketing') ?></th>
                            <th scope="col"><?php echo esc_html__('Affiliate Notes', 'relay-affiliate-marketing') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['payouts'] ?? [] as $payout) { ?>
                            <tr>
                                <td scope="row" class="">
                                    <?php echo esc_html($payout['paid_at']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php echo esc_html($payout['formatted_amount']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php echo esc_html(strtoupper($payout['status'])) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php echo esc_html(ucwords($payout['payment_type'])) ?>
                                    <?php if (!empty($payout['coupon_code'])) { ?>
                                        <span class="wprelay-coupon" style="display:flex; gap: .2rem"><?php echo esc_html($payout['coupon_code']) ?>
                                            <span data-content="<?php echo esc_html($payout['coupon_code']) ?>" class="wprelay-copy-content"><i class="rwp rwp-copy"></i></span>
                                        </span>
                                        <?php if (isset($payout['is_coupon_usage_available']) && !$payout['is_coupon_usage_available']) { ?>
                                            <span>
                                                <?php echo esc_attr('Coupon Used', 'relay-affiliate-marketing') ?>
                                            </span>
                                        <?php } ?>
                                    <?php } ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php echo esc_html($payout['affiliate_note']) ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div>
                <?php include('pagination.php') ?>
            </div>
    </div>
<?php } ?>
</div>

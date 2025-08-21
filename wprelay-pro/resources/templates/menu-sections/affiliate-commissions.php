<?php
defined("ABSPATH") or exit;
?>

<div>
    <div class="wprelay-section-commissions">
        <div class="commissions-header">
            <div class='commissions-main-header'>
                <i class="rwp rwp-commissions"></i>
                <h3><?php echo esc_html__('Commissions', 'relay-affiliate-marketing') ?></h3>
            </div>
        </div>
        <?php if (empty($data['commissions'])) { ?>
            <div class="wprelay-no-commissions">
                <h5><?php echo esc_html__('No Commissions Recently', 'relay-affiliate-marketing') ?></h5>
                <p><?php echo esc_html__("There's nothing here... for now anyway. But if you stick around, there could be plenty of commission for you soon! Hurry back for more awesomesauce!", 'relay-affiliate-marketing') ?></p>
            </div>
        <?php } else { ?>
            <div class="commissions-table">
                <table>
                    <thead>
                        <tr>
                            <th scope="col"><?php echo esc_html__('Order', 'relay-affiliate-marketing') ?></th>
                            <th scope="col"><?php echo esc_html__('Customer', 'relay-affiliate-marketing') ?> </th>
                            <th scope="col"><?php echo esc_html__('Order Amount', 'relay-affiliate-marketing') ?></th>
                            <th scope="col"><?php echo esc_html__('Commission Amount', 'relay-affiliate-marketing') ?></th>
                            <th scope="col"><?php echo esc_html__('Type', 'relay-affiliate-marketing') ?></th>
                            <th scope="col"><?php echo esc_html__('Status', 'relay-affiliate-marketing') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['commissions'] ?? [] as $commission) { ?>
                            <tr>
                                <td scope="row" class="customer-name">
                                    #<?php echo esc_html($commission['woo_order_id']) ?>
                                </td>
                                <td scope="row" class="customer-name">
                                    <?php echo esc_html($commission['customer_name']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php echo esc_html($commission['formatted_order_amount']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php echo esc_html($commission['formatted_commission_amount']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php echo esc_html(ucwords($commission['commission_type'])) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php echo esc_html(ucwords($commission['status'])) ?>
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

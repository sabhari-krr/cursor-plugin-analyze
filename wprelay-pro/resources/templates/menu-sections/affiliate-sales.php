<?php
defined("ABSPATH") or exit;
?>

<div class="wprelay-section-orders">
    <div class="sales-header">
        <div class='sales-main-header'>
            <i class="rwp rwp-sales"></i>
            <h3><?php echo esc_html__('Sales', 'relay-affiliate-marketing') ?></h3>
        </div>
    </div>

    <?php if (empty($data['sales'])) { ?>
        <div class="wprelay-no-sales">
            <h5><?php echo esc_html__('No Sales Yet', 'relay-affiliate-marketing') ?></h5>
            <p><?php echo esc_html__('At this time, there are no sales data available for this affiliate. We appreciate your continued partnership and encourage you to reach out if you have any questions or require further assistance', 'relay-affiliate-marketing') ?></p>
        </div>
    <?php } else { ?>

        <div class="sales-table">
            <table>
                <thead>
                    <tr>
                        <th scope="col"><?php echo esc_html__('Customer', 'relay-affiliate-marketing') ?></th>
                        <th scope="col"><?php echo esc_html__('Order', 'relay-affiliate-marketing') ?></th>
                        <th scope="col"><?php echo esc_html__('Total Amount', 'relay-affiliate-marketing') ?></th>
                        <th scope="col"><?php echo esc_html__('Medium', 'relay-affiliate-marketing') ?></th>
                        <th scope="col"><?php echo esc_html__('Status', 'relay-affiliate-marketing') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['sales'] ?? [] as $sale) { ?>
                        <tr>
                            <td class="customer-name">
                                <?php echo esc_html($sale['customer_name']) ?>
                            </td>
                            <td class="px-6 py-4">
                                #<?php echo esc_html($sale['woo_order_id']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo esc_html($sale['formatted_amount']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo esc_html(ucwords($sale['medium'])) ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo esc_html(ucwords($sale['status'])) ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div>
            <?php include('pagination.php') ?>
        </div>
    <?php } ?>
</div>

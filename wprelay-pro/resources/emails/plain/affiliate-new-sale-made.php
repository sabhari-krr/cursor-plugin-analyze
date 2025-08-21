<?php
defined("ABSPATH") or exit;
?>

<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <title></title>
    <!--[if !mso]><!-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body style="background-color: #ffffff; word-spacing:normal;">
<div style="">
    <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="margin:0px auto;max-width:600px;">
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
            <tbody>
            <tr>
                <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
                    <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
                    <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                            <tbody>
                            <tr>
                                <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                    <div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:16px;line-height:1.5;text-align:left;color:#000000;">
                                        <h3><?php echo esc_html__("🎉 Great News! A Sale Has Been Made Through Your Referral", 'relay-affiliate-marketing'); ?></h3>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                    <div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:14px;line-height:1.5;text-align:left;color:#000000;">
                                        <p><?php echo esc_html__("A new sale has been successfully made using your referral link! Here are the details:", 'relay-affiliate-marketing'); ?></p>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                    <table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:14px;line-height:22px;table-layout:auto;width:100%;border:none;">
                                        <tr style="border-bottom:1px solid #ecedee;text-align:left;padding:15px 0;">
                                            <th style="padding:0 15px 0 0;"><?php echo esc_html__("Order Id", 'relay-affiliate-marketing'); ?>:</th>
                                            <td style="padding:0 15px;">{{order_id}}</td>
                                        </tr>
                                        <tr style="border-bottom:1px solid #ecedee;text-align:left;padding:15px 0;">
                                            <th style="padding:0 15px 0 0;"><?php echo esc_html__("Sale Amount", 'relay-affiliate-marketing'); ?>:</th>
                                            <td style="padding:0 15px;">{{order_amount}}</td>
                                        </tr>
                                        <tr style="border-bottom:1px solid #ecedee;text-align:left;padding:15px 0;">
                                            <th style="padding:0 15px 0 0;"><?php echo esc_html__("Customer Name", 'relay-affiliate-marketing'); ?>:</th>
                                            <td style="padding:0 15px;">{{customer_name}}</td>
                                        </tr>
                                        <tr style="border-bottom:1px solid #ecedee;text-align:left;padding:15px 0;">
                                            <th style="padding:0 15px 0 0;"><?php echo esc_html__("Customer Email", 'relay-affiliate-marketing'); ?>:</th>
                                            <td style="padding:0 15px;">{{customer_email}}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                    <div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:14px;line-height:1.5;text-align:left;color:#000000;">
                                        <p><?php echo esc_html__("Keep up the amazing work and continue sharing your referral link to maximize your earnings.", 'relay-affiliate-marketing'); ?></p>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:0px;padding:20px 25px;">
                                    <a href="{{affiliate_dashboard}}"  style="background-color:#0073aa;color:#ffffff;padding:12px 20px;text-decoration:none;border-radius:5px;font-size:14px;">
                                        <?php echo esc_html__("View Your Dashboard", 'relay-affiliate-marketing'); ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                    <div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:14px;line-height:1.5;text-align:left;color:#000000;">
                                        <p><?php echo esc_html__("Best regards,", 'relay-affiliate-marketing'); ?><br><strong>{{store_name}}</strong></p>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--[if mso | IE]></td></tr></table><![endif]-->
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <!--[if mso | IE]></td></tr></table><![endif]-->
</div>
</body>

</html>

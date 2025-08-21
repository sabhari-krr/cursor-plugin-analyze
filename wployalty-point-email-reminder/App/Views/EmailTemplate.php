<?php
/**
 * Points Rundown Email Template
 * Variables: $user_data->email, $user_data->points, $redeem_example (e.g., "100 points = $10 off")
 */

// Dynamically fetch the site URL and My Account page link
//$site_url       = esc_url( home_url() ); // Base site URL
$my_account_url = esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); // My Account page URL
$redeem_url     = add_query_arg( 'loyalty_reward', '', $my_account_url ); // Append 'loyalty_reward' query parameter
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_html_e( 'Your Loyalty Points Balance', 'wployalty-point-email-reminder' ); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .email-header {
            background-color: #4f47eb;
            color: #ffffff;
            text-align: center;
            padding: 20px;
        }

        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }

        .email-content {
            padding: 20px;
            color: #333;
        }

        .email-content p {
            font-size: 16px;
            line-height: 1.5;
        }

        .email-content .highlight {
            color: #4f47eb;
            font-weight: bold;
        }

        .email-button {
            display: block;
            text-align: center;
            background-color: #4f47eb;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin: 20px auto;
            width: fit-content;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .email-button:hover {
            background-color: #1428b1;
        }

        .email-footer {
            text-align: center;
            padding: 15px;
            background-color: #f3f4f6;
            font-size: 14px;
            color: #666;
        }

        .email-footer a {
            color: #4f47eb;
            text-decoration: none;
        }

        .email-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="email-header">
        <h1><?php printf( __( 'Hello %s!', 'wployalty-point-email-reminder' ), esc_html( $user_data->user_email ) ); ?></h1>
    </div>
    <div class="email-content">
        <p><?php esc_html_e( 'We wanted to let you know your current loyalty points balance:', 'wployalty-point-email-reminder' ); ?></p>
        <p class="highlight"><?php printf( __( 'You have %d points available.', 'wployalty-point-email-reminder' ), esc_html( $user_data->points ) ); ?></p>
        <p><?php esc_html_e( 'Redeem your points for exciting rewards, such as:', 'wployalty-point-email-reminder' ); ?></p>
        <p class="highlight"><?php echo esc_html( $redeem_example ); ?></p>
        <a href="<?php echo esc_url( $redeem_url ); ?>" class="email-button">
			<?php esc_html_e( 'Redeem Your Points Now', 'wployalty-point-email-reminder' ); ?>
        </a>
    </div>
    <div class="email-footer">
        <p><?php esc_html_e( 'Thank you for being a loyal customer!', 'wployalty-point-email-reminder' ); ?></p>
        <p>
            <a href="<?php echo esc_url( $my_account_url ); ?>">
				<?php esc_html_e( 'Visit Your Account', 'wployalty-point-email-reminder' ); ?>
            </a>
        </p>
    </div>
</div>
</body>
</html>

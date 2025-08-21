<?php

namespace WLPR\App\Controllers;

use WLPR\App\Models\UserPointsModel;

class AdminController {
	/**
	 * @return void
	 */
	public static function scheduleEmailReminder() {
		$interval    = get_option( 'points_reminder_interval', 'monthly' );
		$custom_days = get_option( 'points_reminder_custom_days', 30 );
		switch ( $interval ) {
			case 'monthly':
				$schedule_time = MONTH_IN_SECONDS;
				break;
			case 'bimonthly':
				$schedule_time = MONTH_IN_SECONDS * 2;
				break;
			case 'custom':
				$schedule_time = $custom_days * DAY_IN_SECONDS;
				break;
			default:
				$schedule_time = MONTH_IN_SECONDS; // Fallback
		}

// Schedule the action if not already scheduled
		if ( ! as_next_scheduled_action( 'wployalty_points_remainder_schedule' ) ) {
			as_schedule_recurring_action( time(), $schedule_time, 'wployalty_points_remainder_schedule' );
		}
	}

	/**
	 * @return void
	 */
	public static function clearEmailReminder() {
		$scheduled_actions = as_get_scheduled_actions( [
			'hook' => 'wployalty_points_remainder_schedule',
		] );

		foreach ( $scheduled_actions as $action ) {
			as_unschedule_action( 'wployalty_points_remainder_schedule' );
		}
	}

	/**
	 * @return void
	 */
	public static function sendPointsReminderFromScheduler() {
		$users = UserPointsModel::getUsersWithPoints();
		if ( ! empty( $users ) ) {
			self::sendPointsReminder( $users );
		}
	}

	/**
	 * @param $users
	 *
	 * @return void
	 */
	public static function sendPointsReminder( $users ) {
		$admin_email = get_option( 'admin_email' );
		add_filter( 'wp_mail_from', function () {
			return 'noreply@yourdomain.com';
		} );
		add_filter( 'wp_mail_from_name', function () {
			return 'WP Loyalty'; // Set this to plugin or company name
		} );
		foreach ( $users as $user ) {
			$email_content = self::generateEmailContent( $user );
			$subject       = sprintf( esc_html__( 'Your Points Update - %d Points!', 'wployalty-point-email-reminder' ), $user->points );
			$headers       = [
				'Content-Type: text/html; charset=UTF-8',
				'Reply-To: ' . $admin_email // Set the reply-to to the admin email
			];
			wp_mail( $user->user_email, $subject, $email_content, $headers );
		}
//        // Remove the filters after sending the email to avoid affecting other emails
//        remove_filter('wp_mail_from', function () {
//            return 'noreply@yourdomain.com';
//        });
//        remove_filter('wp_mail_from_name', function () {
//            return 'WP Loyalty'; // Set this to your plugin or company name
//        });
	}

	/**
	 * @param $user
	 *
	 * @return false|string
	 */
	private static function generateEmailContent( $user ) {
		ob_start();
		$user_data = $user;
		include plugin_dir_path( __FILE__ ) . '/../Views/EmailTemplate.php';

		return ob_get_clean();
	}
}

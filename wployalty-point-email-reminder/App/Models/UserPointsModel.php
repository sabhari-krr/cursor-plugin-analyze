<?php

namespace WLPR\App\Models;

class UserPointsModel
{
    public static function getUsersWithPoints()
    {
        global $wpdb;
        $loyalty_table = $wpdb->prefix . 'wlr_users';
        $users_table = $wpdb->prefix . 'users';

//        $query = "SELECT u.id, u.points, u.user_email, u.earn_total_point, wp.display_name FROM wp_wlr_users u JOIN wp_users wp ON TRIM(LOWER(u.user_email)) = TRIM(LOWER(wp.user_email));";
//        $query = "SELECT * FROM `{$loyalty_table}`";
        $query = "
    SELECT 
        u.id, 
        u.points, 
        u.user_email, 
        u.earn_total_point, 
        COALESCE(wp.display_name, 'N/A') AS display_name 
    FROM $loyalty_table u
    LEFT JOIN $users_table wp 
    ON TRIM(LOWER(u.user_email)) = TRIM(LOWER(wp.user_email))
    WHERE u.is_allow_send_email = 1 
      AND u.is_banned_user = 0
";


        return $wpdb->get_results($query);
    }


    public static function getUserName($user_email)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_users';

        $query = $wpdb->prepare("SELECT display_name FROM {$table_name} WHERE user_email = %s", $user_email);

        return $wpdb->get_var($query); // Fetch the display name
    }


    public static function getUserById($user_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wlr_users';

        $query = $wpdb->prepare("SELECT id, points, user_email, earn_total_point FROM {$table_name} WHERE id = %d", $user_id);

        return $wpdb->get_row($query);
    }
}

<?php

namespace WLPR\App\Helpers;

defined('ABSPATH') or die();

class WC {
    /**
     * Checks if the current user has admin privilege.
     *
     * This method checks if the current user has the 'manage_woocommerce' capability.
     *
     * @return bool True if the user has admin privilege, false otherwise.
     */
    public static function hasAdminPrivilege()
    {
        if (current_user_can('manage_woocommerce')) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Renders a template file with the given data.
     *
     * This method checks if the template file exists and includes it with the provided data.
     * If the display parameter is true, it echoes the content; otherwise, it returns the content.
     *
     * @param string $file
     * @param array $data
     * @param bool $display
     * @return void|string
     */
    public static function renderTemplate(string $file, array $data = [], bool $display = true)
    {
        $content = '';
        if (file_exists($file)) {
            ob_start();
            extract($data);
            include $file;
            $content = ob_get_clean();
        }
        if ($display) {
            echo $content;
        } else {
            return $content;
        }
    }

    /**
     * Get the email of the current logged in user
     * 
     * @return string
     */
    public static function getLoginUserEmail()
    {
        $user = get_user_by('id', get_current_user_id());
        $user_email = '';
        if (!empty($user)) {
            $user_email = $user->user_email;
        }

        return $user_email;
    }

    /**
     * Create a nonce for security
     * 
     * @param string $nonce_name
     * @return string
     */
    public static function createNonce(string $nonce_name = ''): string
    {
        return wp_create_nonce($nonce_name);
    }

    /**
     * Verify a nonce
     * 
     * @param string $nonce
     * @param string $nonce_name
     * @return bool
     */
    public static function verifyNonce(string $nonce, string $nonce_name = ''): bool
    {
        if (empty($nonce) || empty($nonce_name)) {
            return false;
        }

        return wp_verify_nonce($nonce, $nonce_name);
    }

    /**
     * Check if security is valid (admin privilege + nonce)
     * 
     * @param string $nonce_name
     * @return bool
     */
    public static function isSecurityValid(string $nonce_name = ''): bool
    {
        $nonce = Input::get('wlpr_nonce');
        if (!self::hasAdminPrivilege() || !self::verifyNonce($nonce, $nonce_name)) {
            return false;
        }
        return true;
    }
}
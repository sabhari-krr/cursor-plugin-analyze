<?php

namespace RelayWp\Affiliate\Core\Controllers;

defined("ABSPATH") or exit;

use Error;
use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\PluginHelper;
use RelayWp\Affiliate\App\Helpers\WC;
use RelayWp\Affiliate\App\Helpers\WordpressHelper;
use RelayWp\Affiliate\App\Route;
use Cartrabbit\Request\Request;
use Cartrabbit\Request\Response;
use RelayWp\Affiliate\App\Services\Settings;
use RelayWp\Affiliate\Core\Models\Program;

class LocalDataController
{
    public function getLocalData(Request $request)
    {
        $rwpCurrency = Functions::getSelectedCurrency();
        $currentUserData = wp_get_current_user();
        $affiliateSettings = Settings::get('affiliate_settings');
        $default_program_id = $affiliateSettings['general']['default_program_id'] ?? null;

        $default_program_label = '';
        if (!empty($default_program_id)) {
            $default_program = Program::query()->find($default_program_id);
            $default_program_label = $default_program->title ?? '';
        }

        try {
            $localData = [
                'is_pro' => PluginHelper::isPRO(),
                'plugin_name' => RWPA_PLUGIN_NAME,
                'user' => [
                    'nick_name' => $currentUserData->user_nicename,
                    'email' => $currentUserData->user_email,
                    'url' => $currentUserData->user_url,
                    'is_admin' => $currentUserData->caps['administrator']
                ],
                'nonces' => [
                    'dashboard_nonce' => WordpressHelper::createNonce('dashboard_nonce'),
                    'affiliate_nonce' => WordpressHelper::createNonce('affiliate_nonce'),
                    'program_nonce' => WordpressHelper::createNonce('program_nonce'),
                    'pending_payment_nonce' => WordpressHelper::createNonce('pending_payment_nonce'),
                    'payment_history_nonce' => WordpressHelper::createNonce('payment_history_nonce'),
                    'orders_nonce' => WordpressHelper::createNonce('orders_nonce'),
                    'commissions_nonce' => WordpressHelper::createNonce('commission_nonce'),
                ],
                'common' => [
                    'pagination_limit' => 10,
                    'wlr_apps_nonce' => '', //Woocommerce::create_nonce('wlr_apps_nonce'),
                    'wlr_common_user_nonce' => '',
                    'site_url' => site_url(),
                    'site_icon_url' => get_site_icon_url(),
                    'version' => RWPA_VERSION,
                    'edit_campaign_url' => admin_url('admin.php?page=wp-loyalty-rules#/edit_earn_campaign'),
                ],
                'home_url' => get_home_url(),
                'admin_url' => admin_url(),
                'ajax_url' => admin_url('admin-ajax.php'),
                'ajax_name' => Route::AJAX_NAME,
                'currencies' => array(
                    'default_currency' => $default_currency = get_woocommerce_currency(),
                    'list_of_currency' => WC::getCurrencyList(),
                    'woo_currency_symbol' => WC::getWooCommerceCurrencySymbol($rwpCurrency),
                    'currency_position' => get_option('woocommerce_currency_pos'),
                ),
                'version' => RWPA_VERSION,
                'affiliate_registration_url' => static::getRegistrationPageURL(),
                'wp_pages' => static::getWpPages(),
                'paypal_payment_available_for_chosen_currency' => apply_filters('rwpa_paypal_payment_available_for_currency', false, $rwpCurrency),
                'coupon_payment_available_for_chosen_currency' => apply_filters('rwpa_coupon_payment_available_for_currency', false, $rwpCurrency),
                'default_program_id' => $affiliateSettings['general']['default_program_id'] ?? null,
                'default_program' => !empty($default_program_id) ? [
                    'label' => $default_program_label,
                    'value' => $affiliateSettings['general']['default_program_id']
                ] : null,
                'labels' => static::getLabels(),
            ];

            $localize = apply_filters('rwpa_pro_local_data', $localData);

            return Response::success($localize);
        } catch (\Exception | Error $exception) {

            return Response::error(PluginHelper::serverErrorMessage());
        }
    }

    public static function getWpPages()
    {
        $pages = get_pages();

        $data = [];

        foreach ($pages as $index => $page) {
            $data[$index]['value'] = $page->ID;
            $data[$index]['label'] = $page->post_name;
            $data[$index]['page_url'] = get_permalink($page->ID);
        }

        return $data;
    }

    protected static function getRegistrationPageURL()
    {
        $page_id = Settings::get('affiliate_settings.general.affiliate_registration_page_id');

        if (empty($page_id)) return null;

        return get_permalink($page_id);
    }

    public static function getLabels()
    {
        return [
            'search' => __('Search', 'relay-affiliate-marketing'),
            'nav_links' => [
                'dashboard' => __('Dashboard', 'relay-affiliate-marketing'),
                'manage' => __('Manage', 'relay-affiliate-marketing'),
                'affiliates' => __('Affiliates', 'relay-affiliate-marketing'),
                'orders' => __('Orders', 'relay-affiliate-marketing'),
                'commissions' => __('Commissions', 'relay-affiliate-marketing'),
                'payouts' => __('Payouts', 'relay-affiliate-marketing'),
                'settings' => __('Settings', 'relay-affiliate-marketing'),
            ],
            'dashboard' => [
                'welcome_back' => __('Welcome Back', 'relay-affiliate-marketing'),
                'benchmarks' => [
                    'pending_affiliates' => __('Pending Affiliates', 'relay-affiliate-marketing'),
                    'total_sales' => __('Total Sales', 'relay-affiliate-marketing'),
                    'total_affiliates' => __('Total Affiliates', 'relay-affiliate-marketing'),
                    'total_payouts' => __('Total Payouts', 'relay-affiliate-marketing'),
                    'view_applicants' => __('View Applicants', 'relay-affiliate-marketing'),
                    'view_sales' => __('View Sales', 'relay-affiliate-marketing'),
                    'view_affiliates' => __('View Affiliates', 'relay-affiliate-marketing'),
                    'view_payouts' => __('View Payouts', 'relay-affiliate-marketing'),
                ],
                'recent_activities' => [
                    'recent_activities_title' => __('Recent Activities', 'relay-affiliate-marketing'),
                    'orders' => __('Orders', 'relay-affiliate-marketing'),
                    'commissions' => __('Commissions', 'relay-affiliate-marketing'),
                    'visits' => __('Visits', 'relay-affiliate-marketing'),
                    'order_headers' => static::getOrderTableHeaders(),
                    'commission_headers' => static::getCommissionTableHeaders(),
                    'visits_headers' => static::getVisitsTableHeaders(),
                ],
                'assistance' => [
                    'title' => __('Hey, do you need any assistance?', 'relay-affiliate-marketing'),
                    'description' => __("If you have questions or need help with WPRelay, let us know and we'll be happy to assist.", 'relay-affiliate-marketing'),
                    'support_team_button_text' => __('Talk with support team', 'relay-affiliate-marketing'),
                    'feature_request_button_text' => __('Feature Requests', 'relay-affiliate-marketing'),
                ],
                'spread_links' => [
                    'title' => __('Spread the word about your registration form!', 'relay-affiliate-marketing'),
                    'description' => __("Share your registration form link on social media to reach more people and increase participation in your affiliate program.", 'relay-affiliate-marketing'),
                ]
            ],
            'create_affiliate' => [
                'add_affiliate_title' => __('Add Affiliate', 'relay-affiliate-marketing'),
                'save_affiliate' => __('Save Affiliate', 'relay-affiliate-marketing'),
                'program' => __('Program', 'relay-affiliate-marketing'),
                'program_select_description' => __('Select one program to associate with this Affiliate.', 'relay-affiliate-marketing'),
                'personal_info' => __('Personal Info', 'relay-affiliate-marketing'),
                'personal_info_description' => __('Please provide the necessary personal information of the affiliate, including their name and email address.', 'relay-affiliate-marketing'),
                'first_name' => __('First Name', 'relay-affiliate-marketing'),
                'last_name' => __('Last Name', 'relay-affiliate-marketing'),
                'email' => __('Email', 'relay-affiliate-marketing'),
                'referral_code' => __('Referral Code', 'relay-affiliate-marketing'),
                'referral_code_confirmation_title' => __('Are you sure?', 'relay-affiliate-marketing'),
                'referral_code_confirmation_description' => __('Changing the referral code will update the existing one invalid.', 'relay-affiliate-marketing'),
                'cancel' => __('Cancel', 'relay-affiliate-marketing'),
                'update' => __('Update', 'relay-affiliate-marketing'),
                'submit' => __('Submit', 'relay-affiliate-marketing'),
                'are_you_sure' => __('Are you sure?', 'relay-affiliate-marketing'),
                'referral_exist' => __('The Coupon With Same Code is Already Exists Please Try With Different One.', 'relay-affiliate-marketing'),
                'referral_code_not_changed' => __('Referral Code Not changed', 'relay-affiliate-marketing'),
                'referral_code_required' => __('Referral Code cannot be empty', 'relay-affiliate-marketing'),
                'referral_change_confirmation_description' => __('Changing the referral code will update the existing one invalid.', 'relay-affiliate-marketing'),
            ],
            'update_program' => static::getUpdateProgramLabels(),
            'rules' => static::getRulesTranslation(),
            'programs' => [
                'manage' => __('Manage', 'relay-affiliate-marketing'),
                'yes' => __('Yes', 'relay-affiliate-marketing'),
                'edit' => __('Edit', 'relay-affiliate-marketing'),
                'program' => __('Program', 'relay-affiliate-marketing'),
                'cancel' => __('Cancel', 'relay-affiliate-marketing'),
                'it' => __('it', 'relay-affiliate-marketing'),
                'programs' => __('Programs', 'relay-affiliate-marketing'),
                'all' => __('All', 'relay-affiliate-marketing'),
                'active' => __('Active', 'relay-affiliate-marketing'),
                'activate' => __('Activate', 'relay-affiliate-marketing'),
                'draft' => __('Draft', 'relay-affiliate-marketing'),
                'archive' => __('Archive', 'relay-affiliate-marketing'),
                'create_program' => __('Create Program', 'relay-affiliate-marketing'),
                'filter_by_status' => __('Filter by Status', 'relay-affiliate-marketing'),
                'are_you_sure' => __('Are you sure?', 'relay-affiliate-marketing'),
                'program_will_be_drafted' => __('This Program will be Drafted?', 'relay-affiliate-marketing'),
                'program_will_be_activated' => __('This Program will be Activated?', 'relay-affiliate-marketing'),
                'program_will_be_archived' => __('This Program will be Archived?', 'relay-affiliate-marketing'),
                'program_draft_description' => __("If the program is transitioned to a draft status, it may adversely impact the functionality of affiliate links for associated affiliates.", 'relay-affiliate-marketing'),
                'program_not_found' => __('The program you are looking for is not found', 'relay-affiliate-marketing'),
                'program_not_found_description' => __("Uh oh, your program list is looking a little empty! Looks like the search didn't return any results", 'relay-affiliate-marketing'),
                'set_active' => __("Set Active", 'relay-affiliate-marketing'),
                'program_headers' => [
                    'name' => __('Program Name', 'relay-affiliate-marketing'),
                    'total_revenue' => __('Total Revenue', 'relay-affiliate-marketing'),
                    'total_affiliates' => __('Total Affiliates', 'relay-affiliate-marketing'),
                    'affiliate_commission' => __('Affiliate Commission', 'relay-affiliate-marketing'),
                    'commission_type' => __('Commission Type', 'relay-affiliate-marketing'),
                    'customer_discount' => __('Customer Discount', 'relay-affiliate-marketing'),
                    'actions' => __('Actions', 'relay-affiliate-marketing'),
                    'search' => __('search', 'relay-affiliate-marketing'),
                ]
            ],
            'orders' => [
                'all_orders' => __('All Orders', 'relay-affiliate-marketing'),
                'filter_by_status' => __('Filter by Status', 'relay-affiliate-marketing'),
                'orders_empty' => __('The sale detail you are looking for is not found', 'relay-affiliate-marketing'),
                'orders_empty_description' => __("Uh oh, your order list is looking a little empty! Looks like the search didn't return any results.", 'relay-affiliate-marketing'),
                'order_headers' => static::getOrderTableHeaders(),
            ],
            'commissions' => [
                'all_commissions' => __('All Commissions', 'relay-affiliate-marketing'),
                'filter_by_status' => __('Filter by status', 'relay-affiliate-marketing'),
                'commission_empty' => __('The commission detail you are looking for is not found.', 'relay-affiliate-marketing'),
                'commission_empty_description' => __("Uh oh, your commission list is looking a little empty! Looks like the search didn't return any results.", 'relay-affiliate-marketing'),
                'commission_headers' => static::getCommissionTableHeaders(),
            ],
            'payouts' => [
                'payouts' => __('Payouts', 'relay-affiliate-marketing'),
                'pending_payment_title' => __('Pending Payments', 'relay-affiliate-marketing'),
                'payment_history_title' => __('Payment History', 'relay-affiliate-marketing'),
                'pay' => __('pay', 'relay-affiliate-marketing'),
                'apply' => __('Apply', 'relay-affiliate-marketing'),
                'pending_payments' => [
                    'pay' => __('pay', 'relay-affiliate-marketing'),
                    'pending_payment_not_found' => __('The pending payment you are looking for is not found', 'relay-affiliate-marketing'),
                    'pending_payment_not_found_description' => __("Uh oh, pending payment is looking a little empty! Looks like the search didn't return any results.", 'relay-affiliate-marketing'),
                    'table_headers' => [
                        'affiliate' => __('Affiliate', 'relay-affiliate-marketing'),
                        'billing_email' => __('Billing Email', 'relay-affiliate-marketing'),
                        'to_pay' => __('To Pay', 'relay-affiliate-marketing'),
                        'actions' => __('Actions', 'relay-affiliate-marketing'),
                    ],
                    'no_payouts' => __('No Payouts Yet', 'relay-affiliate-marketing'),
                    'no_payouts_description' => __('Uh oh, Your Payouts list is looking a little empty! ', 'relay-affiliate-marketing'),
                ],
                'payment_history' => [
                    'revert' => __('Revert', 'relay-affiliate-marketing'),
                    'coupon_used' => __('Coupon Used', 'relay-affiliate-marketing'),
                    'payment_history_empty_title' => __("The payment history you are looking for is not found", 'relay-affiliate-marketing'),
                    'payment_history_empty_description' => __("Uh oh, payment history is looking a little empty! Looks like the search didn't return any results.", 'relay-affiliate-marketing'),
                    'revert_model' => [
                        'are_you_sure' => __('Are you sure?', 'relay-affiliate-marketing'),
                        'title' => __("This Actions Can't be Undone", 'relay-affiliate-marketing'),
                        'description' => __("The Amount will be added back to Affiliate Commission", 'relay-affiliate-marketing'),
                        'reverted' => __("Reverted", 'relay-affiliate-marketing'),
                        'reason_for_reverting' => __("Reason For Reverting", 'relay-affiliate-marketing'),
                        'cancel' => __("Cancel", 'relay-affiliate-marketing'),
                        'yes_revert_it' => __("Yes, Revert It", 'relay-affiliate-marketing')
                    ],
                    'table_headers' => [
                        'affiliate' => __('Affiliate', 'relay-affiliate-marketing'),
                        'amount_paid' => __('Amount Paid', 'relay-affiliate-marketing'),
                        'status' => __('Status', 'relay-affiliate-marketing'),
                        'payment_details' => __('Payment Details', 'relay-affiliate-marketing'),
                        'actions' => __('Actions', 'relay-affiliate-marketing')
                    ],
                ],

            ],
            'payment_model' => [
                'payout' => __('Payout', 'relay-affiliate-marketing'),
                'cancel' => __('Cancel', 'relay-affiliate-marketing'),
                'review_confirm_text' => __('Payout', 'relay-affiliate-marketing'),
                'total_amount_to_pay' => __('Total Amount to Pay', 'relay-affiliate-marketing'),
                'amount' => __('Amount', 'relay-affiliate-marketing'),
                'payment_source' => __('Payment Method', 'relay-affiliate-marketing'),
                'admin_notes' => __('Admin Notes', 'relay-affiliate-marketing'),
                'affiliate_notes' => __('Affiliate Notes', 'relay-affiliate-marketing'),
                'terms_and_condition' => __('I assure that the payout will be transferred to the affiliate account.', 'relay-affiliate-marketing'),

                //confirmation
                'confirm_payout_title' => __('Confirm Payout', 'relay-affiliate-marketing'),
                'affiliate' => __('Affiliate', 'relay-affiliate-marketing'),
                'payout_type' => __('Payout Type', 'relay-affiliate-marketing'),
                'total_paying_amount' => __('Total Paying Amount', 'relay-affiliate-marketing'),
                'edit_payout' => __('Edit Payout', 'relay-affiliate-marketing'),
                'submit_payment' => __('Submit Payment', 'relay-affiliate-marketing'),
            ],
            'affiliates' => [
                'affiliates' => __('Affiliates', 'relay-affiliate-marketing'),
                'program' => __('Program', 'relay-affiliate-marketing'),
                'choose_any_program' => __('Choose Any Program', 'relay-affiliate-marketing'),
                'create_wc_account' => __('Create Woocommerce Account', 'relay-affiliate-marketing'),
                'are_you_sure' => __('Are you sure?', 'relay-affiliate-marketing'),
                'selected_program' => __('Selected Program', 'relay-affiliate-marketing'),
                'requested_on' => __('Requested On', 'relay-affiliate-marketing'),
                'apply' => __('Apply', 'relay-affiliate-marketing'),
                'cancel' => __('Cancel', 'relay-affiliate-marketing'),
                'create' => __('Create', 'relay-affiliate-marketing'),
                'update' => __('Update', 'relay-affiliate-marketing'),
                'create_affiliate' => __('Create Affiliate', 'relay-affiliate-marketing'),
                'affiliate_will_be' => __('This Affiliate will be', 'relay-affiliate-marketing'),
                'pick_a_program' => __('Pick a program to assign to this affiliate.', 'relay-affiliate-marketing'),
                'approved' => __('Approved', 'relay-affiliate-marketing'),
                'pay_now' => __('Pay Now', 'relay-affiliate-marketing'),
                'change' => __('Change', 'relay-affiliate-marketing'),
                'pending' => __('Pending', 'relay-affiliate-marketing'),
                'rejected' => __('Rejected', 'relay-affiliate-marketing'),
                'no_program_created' => __("No Programs Yet", 'relay-affiliate-marketing'),
                'no_program_created_description' => __("Oops, it appears that you haven't created any programs yet", 'relay-affiliate-marketing'),
                'no_affiliate_found_title' => __('No Affiliate Entry Found', 'relay-affiliate-marketing'),
                'no_affiliate_found_description' => __('The Affiliate detail you are looking for is not found', 'relay-affiliate-marketing'),

                'no_affiliates_added' => __('No Affiliates Added Yet', 'relay-affiliate-marketing'),
                'no_affiliates_added_description' => __('Uh oh, Your affiliate list is looking a little empty! Time to add some new ones. You can import them or suggest some newbies to join the crew.', 'relay-affiliate-marketing'),
                'no_new_applicants' => __("There Are Currently No New Applicants", 'relay-affiliate-marketing'),
                'no_new_applicants_description' => __("Applicants Under your affiliation appear here for review. Sharing the link can help increase the number of applications", 'relay-affiliate-marketing'),
                'no_affiliates_rejected' => __('No Affiliates Have Been Rejected', 'relay-affiliate-marketing'),
                'no_affiliates_rejected_description' => __('Affiliates that have been rejected will be visible here', 'relay-affiliate-marketing'),
                'tabs' => [
                    'sales' => __('Sales', 'relay-affiliate-marketing'),
                    'commissions' => __('Commissions', 'relay-affiliate-marketing'),
                    'transactions' => __('Transactions', 'relay-affiliate-marketing'),
                    'payouts' => __('Payouts', 'relay-affiliate-marketing'),
                    'coupons' => __('Coupons', 'relay-affiliate-marketing'),
                    'profile' => __('Profile', 'relay-affiliate-marketing'),
                ],
                'affiliate_sales' => [
                    'all_orders' => __('All Orders', 'relay-affiliate-marketing'),
                    'order' => __('Order', 'relay-affiliate-marketing'),
                    'date' => __('Date', 'relay-affiliate-marketing'),
                    'customer' => __('Customer', 'relay-affiliate-marketing'),
                    'medium' => __('Medium', 'relay-affiliate-marketing'),
                    'total_amount' => __('Total Amount', 'relay-affiliate-marketing'),
                    'program' => __('Program', 'relay-affiliate-marketing'),
                    'status' => __('Status', 'relay-affiliate-marketing'),
                    'orders_empty' => __('The sale detail you are looking for is not found', 'relay-affiliate-marketing'),
                    'orders_empty_description' => __("Uh oh, your order list is looking a little empty! Looks like the search didn't return any results.", 'relay-affiliate-marketing'),
                    'no_sales_yet' => __('No Sales Yet', 'relay-affiliate-marketing'),
                    'no_sales_yet_description' => __('Oops, it appears that this affiliate has yet to make any sales.', 'relay-affiliate-marketing'),

                    //cards
                    'overview' => __('Overview', 'relay-affiliate-marketing'),
                    'no_of_sales' => __('No of Sales', 'relay-affiliate-marketing'),
                    'total_referrals' => __('Total Referrals', 'relay-affiliate-marketing'),
                    'total_revenue' => __('Total Revenue', 'relay-affiliate-marketing'),
                    'commission_earned' => __('Commission Earned', 'relay-affiliate-marketing'),
                ],

                'affiliate_commissions' => [
                    'all_commissions' => __('All Commissions', 'relay-affiliate-marketing'),
                    'order' => __('Order', 'relay-affiliate-marketing'),
                    'date' => __('Date', 'relay-affiliate-marketing'),
                    'customer' => __('Customer', 'relay-affiliate-marketing'),
                    'commission' => __('Commission', 'relay-affiliate-marketing'),
                    'type' => __('Type', 'relay-affiliate-marketing'),
                    'status' => __('Status', 'relay-affiliate-marketing'),
                    'commission_not_found_title' => __('The commission detail you are looking for is not found', 'relay-affiliate-marketing'),
                    'commission_not_found_description' => __("Uh oh, your commission detail list is looking a little empty! Looks like the search didn't return any results.", 'relay-affiliate-marketing'),
                    'no_commission_yet' => __('No Commission Yet', 'relay-affiliate-marketing'),
                    'no_commission_yet_description' => __('Oops, it appears that this affiliate has yet to make any sales.', 'relay-affiliate-marketing'),

                    //cards
                    'overview' => __('Overview', 'relay-affiliate-marketing'),
                    'no_of_sales' => __('No of Sales', 'relay-affiliate-marketing'),
                    'total_referrals' => __('Total Referrals', 'relay-affiliate-marketing'),
                    'total_revenue' => __('Total Revenue', 'relay-affiliate-marketing'),
                    'commission_earned' => __('Commission Earned', 'relay-affiliate-marketing'),
                ],

                'affiliate_payouts' => [
                    'all_payouts' => __('All Payouts', 'relay-affiliate-marketing'),
                    'date' => __('Date', 'relay-affiliate-marketing'),
                    'amount' => __('Amount', 'relay-affiliate-marketing'),
                    'status' => __('Status', 'relay-affiliate-marketing'),
                    'reverted' => __('Reverted', 'relay-affiliate-marketing'),
                    'revert_reason' => __('Revert Reason', 'relay-affiliate-marketing'),
                    'payment_type' => __('Payment Type', 'relay-affiliate-marketing'),
                    'affiliate_notes' => __('Affiliate Notes', 'relay-affiliate-marketing'),
                    'admin_notes' => __("Admin Notes", 'relay-affiliate-marketing'),
                    'payout_not_found_title' => __('The payout detail you are looking for is not found', 'relay-affiliate-marketing'),
                    'payout_not_found_description' => __("Uh oh, your payout detail list is looking a little empty! Looks like the search didn't return any results.", 'relay-affiliate-marketing'),
                    'no_payouts_yet' => __('No Payouts Yet', 'relay-affiliate-marketing'),
                    'no_payouts_yet_description' => __('Uh oh, Your Payouts list is looking a little empty! Time to add some new ones.', 'relay-affiliate-marketing'),

                    //cards
                    'overview' => __('Overview', 'relay-affiliate-marketing'),
                    'unpaid_commission' => __('Unpaid Commissions', 'relay-affiliate-marketing'),
                    'total_paid' => __('Total Paid', 'relay-affiliate-marketing'),
                    'commission_earned' => __('Commission Earned', 'relay-affiliate-marketing'),
                ],

                'affiliate_transactions' => [
                    'all_transactions' => __('All Transactions', 'relay-affiliate-marketing'),
                    'date' => __('Date', 'relay-affiliate-marketing'),
                    'amount' => __('Amount', 'relay-affiliate-marketing'),
                    'type' => __('Type', 'relay-affiliate-marketing'),
                    'system_notes' => __('System Notes', 'relay-affiliate-marketing'),
                    'transaction_not_found_title' => __('The transaction detail you are looking for is not found', 'relay-affiliate-marketing'),
                    'transaction_not_found_description' => __("Uh oh, your transaction detail list is looking a little empty! Looks like the search didn't return any results.", 'relay-affiliate-marketing'),
                    'no_transactions_yet' => __('No Transactions Yet', 'relay-affiliate-marketing'),
                    'no_transactions_yet_description' => __("Oops, it appears that this affiliate has doesn't have any transactions yet", 'relay-affiliate-marketing'),
                ],

                'affiliate_coupons' => [
                    'coupons' => __('Coupons', 'relay-affiliate-marketing'),
                    'coupon_code' => __('Coupon Code', 'relay-affiliate-marketing'),
                    'regenerate' => __('Regenerate', 'relay-affiliate-marketing'),
                    'are_you_sure' => __('Are you sure?', 'relay-affiliate-marketing'),
                    'regenerate_confirmation_description' => __('The Affiliate Program Customer Discount section will be used to generate coupon?', 'relay-affiliate-marketing'),
                    'cancel' => __('Cancel', 'relay-affiliate-marketing'),
                    'regenerate_it' => __('Yes, Regenerate it!', 'relay-affiliate-marketing'),
                    'no_coupons' => __('No, Coupons', 'relay-affiliate-marketing')
                ],
                'affiliate_profile' => [
                    'profile' => __('Profile', 'relay-affiliate-marketing'),
                    'save_changes' => __('Save Changes', 'relay-affiliate-marketing'),
                    'personal_info' => __('Personal Info', 'relay-affiliate-marketing'),
                    'first_name' => __('First Name', 'relay-affiliate-marketing'),
                    'last_name' => __('Last Name', 'relay-affiliate-marketing'),
                    'email' => __('Email', 'relay-affiliate-marketing'),
                    'billing_email' => __('Billing Email', 'relay-affiliate-marketing'),
                    'phone_number' => __('Phone Number', 'relay-affiliate-marketing'),
                    'shipping_address' => __('Shipping Address', 'relay-affiliate-marketing'),
                    'address' => __('Address', 'relay-affiliate-marketing'),
                    'city' => __('City', 'relay-affiliate-marketing'),
                    'zip_code' => __('Zip Code', 'relay-affiliate-marketing'),
                    'country' => __('Country', 'relay-affiliate-marketing'),
                    'state' => __('State', 'relay-affiliate-marketing'),
                    'social_links' => __('Social Links', 'relay-affiliate-marketing'),
                    'affiliate_meta_details_title' => __('Details From Program Registration', 'relay-affiliate-marketing'),
                ]
            ],
            'settings' => [
                'tabs' => [
                    'license' => __('License', 'relay-affiliate-marketing'),
                    'general' => __('General', 'relay-affiliate-marketing'),
                    'affiliates' => __('Affiliates', 'relay-affiliate-marketing'),
                    'emails' => __('Emails', 'relay-affiliate-marketing'),
                    'payments' => __('Payments', 'relay-affiliate-marketing')
                ],
                'license' => [
                    'title' => __('License', 'relay-affiliate-marketing'),
                    'license_key' => __('License Key', 'relay-affiliate-marketing'),
                    'activate' => __('Activate', 'relay-affiliate-marketing'),
                    'activated_message' => __('License activated succssfully', 'relay-affiliate-marketing'),
                    'deactivate' => __('Deactivate', 'relay-affiliate-marketing'),
                    'deactivated_message' => __('License deactivated succssfully', 'relay-affiliate-marketing'),
                    'activated' => __('Activated', 'relay-affiliate-marketing'),
                    'deactivated' => __('Deactivated', 'relay-affiliate-marketing'),
                    'expired' => __('Expired', 'relay-affiliate-marketing'),
                    'expired_message' => __('License is expired', 'relay-affiliate-marketing'),
                ],
                'general' => [
                    'title' => __('General Settings', 'relay-affiliate-marketing'),
                    'save_changes' => __('Save Changes', 'relay-affiliate-marketing'),
                    'cookie_duration_title' => __('Cookie Duration', 'relay-affiliate-marketing'),
                    'cookie_duration_description' => __('Set the expiration for the affiliate tracking cookie', 'relay-affiliate-marketing'),
                    'duration_in_days' => __('Duration in days', 'relay-affiliate-marketing'),
                    'no_of_days' => __('No of days', 'relay-affiliate-marketing'),

                    'commission_settings_title' => __('Commission Settings', 'relay-affiliate-marketing'),
                    'commission_settings_description' => __('Manage the Charges related to shipping and taxes in the commission calculation', 'relay-affiliate-marketing'),
                    'exclude_shipping_title' => __('Exclude Shipping', 'relay-affiliate-marketing'),
                    'exclude_shipping_description' => __('Exclude shipping costs in the calculation of commissions.', 'relay-affiliate-marketing'),
                    'exclude_taxes_title' => __('Exclude Taxes', 'relay-affiliate-marketing'),
                    'exclude_taxes_description' => __('Exclude taxes costs in the calculation of commissions.', 'relay-affiliate-marketing'),

                    'contact_information_title' => __('Contact Information', 'relay-affiliate-marketing'),
                    'contact_information_description' => __('Provide Your Email Address which Relay and affiliates can use to contact you', 'relay-affiliate-marketing'),
                    'merchant_name_title' => __('Merchant Name', 'relay-affiliate-marketing'),
                    'merchant_email' => __('Email Address', 'relay-affiliate-marketing'),

                    'customize_theme_title' => __('Customize Theme As you want', 'relay-affiliate-marketing'),
                    'customize_theme_description' => __('Choose the Primary and Secondary Color', 'relay-affiliate-marketing'),
                    'primary_color_title' => __('Primary Color', 'relay-affiliate-marketing'),
                    'secondary_color_title' => __('Secondary Color', 'relay-affiliate-marketing'),
                    'deactivate' => __('Deactivate', 'relay-affiliate-marketing'),
                ],
                'affiliates' => [
                    'title' => __('Affiliate Settings', 'relay-affiliate-marketing'),
                    'save_changes' => __('Save Changes', 'relay-affiliate-marketing'),
                    'registration_page_title' => __('Registration Page', 'relay-affiliate-marketing'),
                    'registration_page_description' => __("Select the Affiliate registration page that you've generated using the [rwpa_affiliate_go_registration_form] shortcode.", 'relay-affiliate-marketing'),
                    'allow_affiliate_registration' => __("Allow Affiliate Registration", 'relay-affiliate-marketing'),
                    'registration_page_url' => __("Registration Page URL", 'relay-affiliate-marketing'),
                    'your_shortcode' => __("Your shortcode", 'relay-affiliate-marketing'),
                    'select_program_description' => __("Select a program to assign when affiliate registration", 'relay-affiliate-marketing'),

                    'recaptcha_title' => __('Google Recaptcha V3', 'relay-affiliate-marketing'),
                    'recaptcha_description' => __('RECAPTCHA is a free service that protects your site from spam and abuse', 'relay-affiliate-marketing'),
                    'site_key' => __('Site Key', 'relay-affiliate-marketing'),
                    'secret_key' => __('Secret Key', 'relay-affiliate-marketing'),

                    'auto_approve_commission_title' => __('Commission', 'relay-affiliate-marketing'),
                    'auto_approve_commission_description' => __('Auto Approve Affiliate Commission', 'relay-affiliate-marketing'),
                    'auto_approve_commission' => __('Auto Approve Commission', 'relay-affiliate-marketing'),
                    'immediate' => __('Immediate', 'relay-affiliate-marketing'),
                    'delay' => __('Delay', 'relay-affiliate-marketing'),
                    'after_n_no_of_days' => __('After N no of Days', 'relay-affiliate-marketing'),
                    'delay_description' => __('Setting 0 days will lead to immediate approval', 'relay-affiliate-marketing'),

                    'customize_order_status_title' => __('Customize Order Status', 'relay-affiliate-marketing'),
                    'customize_order_status_description' => __('Customize your order status to either receive the commission amount upon successful completion, or reject the commission if the order fails.', 'relay-affiliate-marketing'),
                    'successful_order_status' => __('Successful Order Status', 'relay-affiliate-marketing'),
                    'failure_order_status' => __('Failure Order Status', 'relay-affiliate-marketing'),

                    'affiliate_url_options_title' => __('Affiliate URL options', 'relay-affiliate-marketing'),
                    'affiliate_url_options_description' => __('Modify the Affiliate URL variable as needed', 'relay-affiliate-marketing'),
                    'url_variable' => __('URL Variable', 'relay-affiliate-marketing'),
                    'url_variable_preview' => __('Affiliates can view referral URLs by appending either their affiliate id or username.', 'relay-affiliate-marketing'),
                ],
                'emails' => [
                    'affiliate_email_tab' => __('Affiliate Emails', 'relay-affiliate-marketing'),
                    'admin_email_tab' => __('Admin Emails', 'relay-affiliate-marketing'),

                    'affiliate_email_title' => __('Affiliate Email Settings', 'relay-affiliate-marketing'),
                    'save_changes' => __('Save Changes', 'relay-affiliate-marketing'),

                    'affiliate_approved_email_title' => __('Affiliate Approved Email', 'relay-affiliate-marketing'),
                    'affiliate_approved_email_description' => __('Sent to the affiliate when their account is approved in the program', 'relay-affiliate-marketing'),

                    'affiliate_rejected_email_title' => __('Affiliate Rejected Email', 'relay-affiliate-marketing'),
                    'affiliate_rejected_email_description' => __('Sent to the affiliate when their account is rejected in the program', 'relay-affiliate-marketing'),
                    'commission_approved_email_title' => __('Commission Approved Email', 'relay-affiliate-marketing'),
                    'commission_approved_email_description' => __('Sent to the affiliate when their commission is approved', 'relay-affiliate-marketing'),
                    'commission_rejected_email_title' => __('Commission Rejected Email', 'relay-affiliate-marketing'),
                    'commission_rejected_email_description' => __('Sent to the affiliate when their commission is rejected', 'relay-affiliate-marketing'),
                    'payment_processed_email_title' => __('Payment Processed Email', 'relay-affiliate-marketing'),
                    'payment_processed_email_description' => __('Sent to the affiliate when their payments are processed', 'relay-affiliate-marketing'),

                    'admin_email_title' => __('Admin Email Settings', 'relay-affiliate-marketing'),

                    'affiliate_registration_title' => __('New Affiliate Registration Email', 'relay-affiliate-marketing'),
                    'affiliate_registration_description' => __('Sent to the store owner when a new affiliate registers', 'relay-affiliate-marketing'),
                    'affiliate_sale_email_title' => __('New Affiliate Sale Email', 'relay-affiliate-marketing'),
                    'affiliate_sale_email_description' => __('The Email is sent to the store owner when an affiliate gets you a sale', 'relay-affiliate-marketing'),
                ]
            ],
            'signup_form' => static::getSignupFormLabels(),
            'validations' => [
                'validation_failed' => __('Validation Failed', 'relay-affiliate-marketing'),
                'create_affiliate' => [
                    /* translators: placeholder description */
                    'first_name_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('First Name', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'program_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Program', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'last_name_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Last Name', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'email_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Email', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'not_a_valid_email' => vsprintf(esc_html__('Not a valid %s', 'relay-affiliate-marketing'), [__('Email', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'referral_code_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Referral code', 'relay-affiliate-marketing')]),
                ],
                'payout_model' => [
                    /* translators: placeholder description */
                    'admin_notes_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Admin Notes', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'amount_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Amount', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'payment_source_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Payment Source', 'relay-affiliate-marketing')]),
                    'missing_billing_email' => __('Missing billing email for affiliate', 'relay-affiliate-marketing'),
                    'amount_less_than' => __('Amount must be less than', 'relay-affiliate-marketing'),
                    'value_greater_than_zero' => __('Value must be greater than zero', 'relay-affiliate-marketing'),
                    /* translators: placeholder description */
                    'affiliate_notes_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Affiliate Notes', 'relay-affiliate-marketing')]),
                    'accept_terms_and_condition' => __('Accept the terms and conditions', 'relay-affiliate-marketing'),
                ],
                'affiliate_profile' => [
                    /* translators: placeholder description */
                    'phone_number_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Phone Number', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'phone_number_min_max' => vsprintf(esc_html__('%1$s must be at least %2$d digits and at most %3$d', 'relay-affiliate-marketing'), [__('Phone Number', 'relay-affiliate-marketing'), 4, 20]),
                    /* translators: placeholder description */
                    'first_name_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('First Name', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'last_name_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Last Name', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'email_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Email', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'not_a_valid_email' => vsprintf(esc_html__('Not a valid %s', 'relay-affiliate-marketing'), [__('Email', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'address_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Address', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'city_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('City', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'zipcode_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Zip Code', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'country_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Country', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'invalid_url' => esc_html__('Invalid url', 'relay-affiliate-marketing'),
                ],
                'settings' => [
                    'license' => [
                        /* translators: placeholder description */
                        'license_key_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('License Key', 'relay-affiliate-marketing')]),
                        /* translators: placeholder description */
                        'invalid_license_key' => vsprintf(esc_html__('Invalid %s', 'relay-affiliate-marketing'), [__('License Key', 'relay-affiliate-marketing')]),
                    ],
                    'general' => [
                        /* translators: placeholder description */
                        'cookie_duration_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Cookie Duration', 'relay-affiliate-marketing')]),
                        /* translators: placeholder description */
                        'exclude_shipping_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Exclude Shipping', 'relay-affiliate-marketing')]),
                        /* translators: placeholder description */
                        'exclude_taxes_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Exclude Taxes', 'relay-affiliate-marketing')]),

                        /* translators: placeholder description */
                        'merchant_name_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Merchant Name', 'relay-affiliate-marketing')]),
                        /* translators: placeholder description */
                        'merchant_email_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Merchant Email', 'relay-affiliate-marketing')]),
                        /* translators: placeholder description */
                        'merchant_email_email' => vsprintf(esc_html__('Not a valid %s', 'relay-affiliate-marketing'), [__('Email', 'relay-affiliate-marketing')]),
                        /* translators: placeholder description */
                        'primary_color_is_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Primary Color', 'relay-affiliate-marketing')]),
                        /* translators: placeholder description */
                        'secondary_color_is_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Secondary Color', 'relay-affiliate-marketing')]),
                    ],
                    'affiliate' => [
                        /* translators: placeholder description */
                        'allow_affiliate_registration_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Allow Affiliate Registration', 'relay-affiliate-marketing')]),
                        /* translators: placeholder description */
                        'affiliate_registration_page_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Affiliate Registration Page', 'relay-affiliate-marketing')]),
                        /* translators: placeholder description */
                        'short_code_name_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Short Code Name', 'relay-affiliate-marketing')]),
                        /* translators: placeholder description */
                        'auto_approve_commission_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Auto Approve Commission', 'relay-affiliate-marketing')]),
                        /* translators: placeholder description */
                        'auto_approve_delay_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Auto Approve Delay in days', 'relay-affiliate-marketing')]),
                        /* translators: placeholder description */
                        'default_program_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Default Program', 'relay-affiliate-marketing')]),
                        /* translators: placeholder description */
                        'successful_order_status_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Successful Order Status', 'relay-affiliate-marketing')]),
                        /* translators: placeholder description */
                        'failure_order_status_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Failure Order Status', 'relay-affiliate-marketing')]),
                        'order_status_duplicate' => __('Successful and failure order statuses cannot be the same', 'relay-affiliate-marketing'),
                        'url_variable_min' => __('Url variable must be at least 3 characters long', 'relay-affiliate-marketing'),
                        /* translators: placeholder description */
                        'url_variable_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Url Variable', 'relay-affiliate-marketing')]),
                    ]
                ],
                'update_program' => [
                    /* translators: placeholder description */
                    'title_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Title', 'relay-affiliate-marketing')]),
                    'start_date_invalid' => __('Invalid date format', 'relay-affiliate-marketing'),
                    'end_date_gt_start_date' => __('End date must be higher than start date', 'relay-affiliate-marketing'),
                    /* translators: placeholder description */
                    'status_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Status', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'commission_type_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Commission Type', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'commission_sub_type_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Commission Sub Type', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'amount_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Amount', 'relay-affiliate-marketing')]),
                    'value_greater_than_zero' => __('Value must be greater than zero', 'relay-affiliate-marketing'),
                    /* translators: placeholder description */
                    'percentage_is_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Percentage', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'percentage_at_most_hundred' => vsprintf(esc_html__('%s must be at most 100', 'relay-affiliate-marketing'), [__('Percentage', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'currency_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Currency', 'relay-affiliate-marketing')]),

                    'tier_duplication' => __('Duplicate Condition and Currency Selected', 'relay-affiliate-marketing'),
                    //customer discount type

                    /* translators: placeholder description */
                    'customer_discount_type_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Customer Discount Type', 'relay-affiliate-marketing')]),
                    'invalid_value' => __('Invalid value', 'relay-affiliate-marketing'),
                    /* translators: placeholder description */
                    'individual_use_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Individual Use', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'exclude_sale_items_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Exclude Sale Item', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'product_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Products', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'category_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Categories', 'relay-affiliate-marketing')]),

                    /* translators: placeholder description */
                    'recurring_commission_enabled' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Recurring commission', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'type_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Type', 'relay-affiliate-marketing')]),

                    'value_must_be_whole_number' => __("The Value must be a whole number", 'relay-affiliate-marketing'),
                    /* translators: placeholder description */
                    'interval_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Interval', 'relay-affiliate-marketing')]),
                ],
                'rules' => [
                    /* translators: placeholder description */
                    'title_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Title', 'relay-affiliate-marketing')]),
                    'start_date_invalid' => __('Invalid date format', 'relay-affiliate-marketing'),
                    'end_date_gt_start_date' => __('End date must be higher than start date', 'relay-affiliate-marketing'),
                    /* translators: placeholder description */
                    'status_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Status', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'type_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Type', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'percentage_is_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Percentage', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'amount_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Amount', 'relay-affiliate-marketing')]),
                    'value_greater_than_zero' => __('Value must be greater than zero', 'relay-affiliate-marketing'),
                    /* translators: placeholder description */
                    'affiliate_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Affiliate', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'product_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Products', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'category_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Categories', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'percentage_at_most_hundred' => vsprintf(esc_html__('%s must be at most 100', 'relay-affiliate-marketing'), [__('Percentage', 'relay-affiliate-marketing')]),
                ],
                'signup_form' => [
                    /* translators: placeholder description */
                    'label_name_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Label Name', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'field_name_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Field Name ', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'title_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Title', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'description_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Description', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'button_text_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Button Text', 'relay-affiliate-marketing')]),
                    'button_text_maximum_50' => __('Button text should not exceed 50 characters', 'relay-affiliate-marketing'),
                    /* translators: placeholder description */
                    'css_style_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Css Style', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'header_text_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Header Text ', 'relay-affiliate-marketing')]),
                    /* translators: placeholder description */
                    'body_text_required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Body Text', 'relay-affiliate-marketing')]),
                ]
            ]
        ];
    }

    public static function getOrderTableHeaders()
    {
        return [
            'order' => __('Order', 'relay-affiliate-marketing'),
            'customer' => __('Customer', 'relay-affiliate-marketing'),
            'email' => __('Email', 'relay-affiliate-marketing'),
            'affiliate' => __('Affiliate', 'relay-affiliate-marketing'),
            'total' => __('Total', 'relay-affiliate-marketing'),
            'status' => __('Status', 'relay-affiliate-marketing'),
        ];
    }

    public static function getCommissionTableHeaders()
    {
        return [
            'order' => __('Order', 'relay-affiliate-marketing'),
            'affiliate' => __('Affiliate', 'relay-affiliate-marketing'),
            'type' => __('Type', 'relay-affiliate-marketing'),
            'total' => __('Total', 'relay-affiliate-marketing'),
            'commission' => __('Commission', 'relay-affiliate-marketing'),
            'status' => __('Status', 'relay-affiliate-marketing'),
        ];
    }

    public static function getVisitsTableHeaders()
    {
        return [
            'orders' => __('Orders', 'relay-affiliate-marketing'),
            'url' => __('URL', 'relay-affiliate-marketing'),
            'ip_address' => __('IP Address', 'relay-affiliate-marketing'),
        ];
    }

    public static function getUpdateProgramLabels()
    {
        return [
            'tabs' => [
                'program_details' => __('Program Details', 'relay-affiliate-marketing'),
                'rules' => __('Rules', 'relay-affiliate-marketing'),
                'signup_form' => __('Signup Form', 'relay-affiliate-marketing'),
                'rules_tab_access_warning' => __('Create program to access this tab', 'relay-affiliate-marketing')
            ],
            'create_page_title' => __('Enter Program Details', 'relay-affiliate-marketing'),
            'edit_page_title' => __('Enter Program Details', 'relay-affiliate-marketing'),
            'save_program' => __('Save Program', 'relay-affiliate-marketing'),
            'active' => __('Active', 'relay-affiliate-marketing'),
            'set_active' => __('Set Active', 'relay-affiliate-marketing'),
            'general' => [
                'general_title' => __('General', 'relay-affiliate-marketing'),
                'general_description' => __('Please provide the essential details for creating the program.', 'relay-affiliate-marketing'),
                'title' => __('Title', 'relay-affiliate-marketing'),
                'description' => __('Description', 'relay-affiliate-marketing'),
                'auto_approve' => __('Auto Approve', 'relay-affiliate-marketing'),
                'note' => __('Note', 'relay-affiliate-marketing'),
                'auto_approve_note' => __('If the Program is selected for default affiliate registration it will decide whether we need to auto approve the affiliate or not.', 'relay-affiliate-marketing'),
                'start_date' => __('Start Date', 'relay-affiliate-marketing'),
                'end_date' => __('End Date', 'relay-affiliate-marketing'),
            ],
            'affiliate_commission' => [
                'affiliate_commission_title' => __('Affiliate Commission', 'relay-affiliate-marketing'),
                'affiliate_commission_description' => __('Please provide details on how an affiliates in this program are to earn commission for referral sales', 'relay-affiliate-marketing'),
                'commission_type_title' => __('Commission Type', 'relay-affiliate-marketing'),
                'type' => __('Type', 'relay-affiliate-marketing'),
                'amount' => __('Amount', 'relay-affiliate-marketing'),
                'percentage' => __('Percentage', 'relay-affiliate-marketing'),
                'rule_based_note' => __('When selecting the rule-based option, you will have access to more advanced configuration settings in the Rules tab. This option applies to each line item , not from the order total.', 'relay-affiliate-marketing'),

                'recurring_option_title' => __('Recurring options', 'relay-affiliate-marketing'),
                'recurring_option_description' => __('Checking this box will enable affiliates to earn commissions either for lifetime or for the first X number of orders.', 'relay-affiliate-marketing'),
                'enable_recurring_commission_title' => __('Enable recurring options', 'relay-affiliate-marketing'),
                'recurring_option_type' => __('Recurring option Type', 'relay-affiliate-marketing'),
                'interval' => __('Interval', 'relay-affiliate-marketing'),
            ],
            'customer_discount' => [
                'customer_discount_title' => __('Customer Discount', 'relay-affiliate-marketing'),
                'customer_discount_description' => __("Specify the Discount to be offered to customers who use an affiliate's link or discount code", 'relay-affiliate-marketing'),
                'discount_type' => __("Discount Type", 'relay-affiliate-marketing'),
                'percentage' => __("Percentage", 'relay-affiliate-marketing'),
                'amount' => __("Amount", 'relay-affiliate-marketing'),
                'allow_free_shipping_title' => __("Allow Free Shipping", 'relay-affiliate-marketing'),
                'allow_free_shipping_description' => __('Check this box if the coupon grants free shipping. A free shipping method must be enabled in your shipping zone and be set to require "a valid free shipping coupon"', 'relay-affiliate-marketing'),
                'limits' => __('Limits', 'relay-affiliate-marketing'),
                'minimum_spend' => __('Minimum Spend', 'relay-affiliate-marketing'),
                'maximum_spend' => __('Maximum Spend', 'relay-affiliate-marketing'),
                'individual_use_only' => __('Individual use Only', 'relay-affiliate-marketing'),
                'individual_use_only_description' => __('Check this box if the coupon cannot be used in conjunction with other coupons.ef', 'relay-affiliate-marketing'),
                'exclude_sale_items_title' => __('Exclude Sale Items', 'relay-affiliate-marketing'),
                'exclude_sale_items_description' => __('Check this box if the coupon should not apply to items on sale. Per-item coupons will only work if the item is not on sale. Per-cart coupons will only work if there are items in the cart that are not on sale', 'relay-affiliate-marketing'),
                'products' => __('Products', 'relay-affiliate-marketing'),
                'exclude_products' => __('Exclude Products', 'relay-affiliate-marketing'),
                'categories' => __('Categories', 'relay-affiliate-marketing'),
                'exclude_categories' => __('Exclude Categories', 'relay-affiliate-marketing'),
                'usage_limit_per_user_title' => __('Usage Limit Per User', 'relay-affiliate-marketing'),
                'usage_limit_per_user_description' => __('When setting usage limits, the default value is 1 use per user. Leaving it unchecked allows unlimited usage, ensuring fair distribution of discounts', 'relay-affiliate-marketing'),
            ],
        ];
    }

    private static function getRulesTranslation()
    {
        return [
            'add_rule' => __('Add Rule', 'relay-affiliate-marketing'),
            'table_headers' => [
                'rule_title' => __('Rule Title', 'relay-affiliate-marketing'),
                'start_date' => __('Start Date', 'relay-affiliate-marketing'),
                'end_date' => __('End Date', 'relay-affiliate-marketing'),
                'based_on' => __('Based on', 'relay-affiliate-marketing'),
                'commission' => __('Commission', 'relay-affiliate-marketing'),
                'affiliates' => __('Affiliates', 'relay-affiliate-marketing'),
                'actions' => __('Actions', 'relay-affiliate-marketing'),
            ],
            'are_you_sure' => __('Are you sure?', 'relay-affiliate-marketing'),
            'rule_will_be_activated' => __('This Rule Will be Activated', 'relay-affiliate-marketing'),
            'rule_will_be_drafted' => __('This Rule Will be Drafted', 'relay-affiliate-marketing'),
            'rule_draft_description' => __("If a rule is in draft status, it may not be included in commission calculations, potentially impacting the earnings for associated affiliates", 'relay-affiliate-marketing'),
            'activate' => __('Activate', 'relay-affiliate-marketing'),
            'cancel' => __('Cancel', 'relay-affiliate-marketing'),
            'heads_up' => __('Heads Up!', 'relay-affiliate-marketing'),
            'rule_note' => __("If the program isn't rule-based, adding and activating rules will earn bonus commissions", 'relay-affiliate-marketing'),
            'program' => __("Program", 'relay-affiliate-marketing'),
            'draft' => __('Draft', 'relay-affiliate-marketing'),
            'it' => __('it', 'relay-affiliate-marketing'),

            'rule_title' => __('Rule Title', 'relay-affiliate-marketing'),
            'commission' => __('Commission', 'relay-affiliate-marketing'),
            'commission_description' => __('Please provide details on how an affiliates in this rule are to earn commission', 'relay-affiliate-marketing'),
            'product' => __('Product', 'relay-affiliate-marketing'),
            'category' => __('Category', 'relay-affiliate-marketing'),
            'products' => __('Products', 'relay-affiliate-marketing'),
            'categories' => __('Categories', 'relay-affiliate-marketing'),
            'commission_type' => __('Commission Type', 'relay-affiliate-marketing'),
            'amount' => __('Amount', 'relay-affiliate-marketing'),
            'percentage' => __('Percentage', 'relay-affiliate-marketing'),
            'conditions' => __('Conditions', 'relay-affiliate-marketing'),
            'conditions_description' => __('Choose the affiliates which this rule can apply', 'relay-affiliate-marketing'),
            'choose_affiliates' => __('Choose Affiliates', 'relay-affiliate-marketing'),
            'general' => __('General', 'relay-affiliate-marketing'),
            'optional' => __('Optional', 'relay-affiliate-marketing'),
            'general_description' => __('Please provide the additional details for creating the rule', 'relay-affiliate-marketing'),
            'description' => __('Description', 'relay-affiliate-marketing'),
            'start_date' => __('Start Date', 'relay-affiliate-marketing'),
            'end_date' => __('End Date', 'relay-affiliate-marketing'),
            'save_rule' => __('Save Rule', 'relay-affiliate-marketing'),
        ];
    }

    private static function getSignupFormLabels()
    {
        return [
            'auto_approve' => __('Auto Approve', 'relay-affiliate-marketing'),
            'customize' => __('Customize', 'relay-affiliate-marketing'),
            'confirmation_page' => __('Confirmation Page', 'relay-affiliate-marketing'),
            'advanced' => __('Advanced', 'relay-affiliate-marketing'),
            'fields' => __('Fields', 'relay-affiliate-marketing'),
            'overview' => __('Overview', 'relay-affiliate-marketing'),
            'title' => __('Title', 'relay-affiliate-marketing'),
            'about_program' => __('About Program', 'relay-affiliate-marketing'),
            'overview_button_text' => __('Button Text', 'relay-affiliate-marketing'),
            'confirmation_header_text' => __('Header Text', 'relay-affiliate-marketing'),
            'confirmation_body_text' => __('Body Text', 'relay-affiliate-marketing'),
            'confirmation_icon_url' => __('Icon Url', 'relay-affiliate-marketing'),
            'confirmation_auto_approve_note' => __("If the program is auto-approved, use the shortcodes {{affiliate_link}}, {{program_name}}, and {{affiliate_dashboard}} in the confirmation screen to automatically replace them with the backend values upon affiliate registration.", 'relay-affiliate-marketing'),
            'enable_css' => __('Enable CSS', 'relay-affiliate-marketing'),
            'enable_css_description' => __('Customize the form by using custom CSS', 'relay-affiliate-marketing'),
            'save' => __('Save', 'relay-affiliate-marketing'),
            'copy_paste_shortcode' => __('Copy and paste this shortcode', 'relay-affiliate-marketing'),
            'copy_past_shortcode_continue' => __("into any page's content area to build your affiliate section", 'relay-affiliate-marketing'),
        ];
    }
}

<?php

defined('ABSPATH') or exit;

return [
    'general_settings' => [
        'cookie_duration' => 90,
        'commission_settings' => [
            'exclude_shipping' => true,
            'exclude_taxes' => false,
        ],
        'contact_information' => [
            'merchant_name' => '',
            'merchant_email' => '',
        ],
        'color_settings' => [
            'primary_color' => '#000000',
            'secondary_color' => '#ffffff',
        ],
    ],
    'affiliate_settings' => [
        'general' => [
            'allow_affiliate_registration' => true,
            'affiliate_registration_page_id' => null,
            'short_code_name' => '[rwpa_affiliate_go_registration_form]',
            'auto_approve_commission' => false,
            'auto_approve_delay_in_days' => 0,
            'default_program_id' => null
        ],
        'successful_order_status' => ['processing', 'completed'],
        'failure_order_status' => ['cancelled', 'refunded'],
        'url_options' => [
            'url_variable' => 'affiliate',
        ],
        'recaptcha' => [
            'site_key' => '',
            'secret_key' => '',
        ]
    ],
    'email_settings' => [
        'affiliate_emails' => [
            'affiliate_approved' => true,
            'affiliate_rejected' => true,
            'commission_approved' => false,
            'commission_rejected' => false,
            'payment_processed' => true,
        ],
        'admin_emails' => [
            'affiliate_registered' => true,
            'affiliate_sale_made' => false,
        ],
    ],
    'payment_settings' => [
        //initial data fetched from plugins
    ]
];


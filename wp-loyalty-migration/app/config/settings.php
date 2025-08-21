<?php

defined('ABSPATH') or exit;

return [
    'migration_settings' => [
        'batch_size' => 100,
        'timeout' => 300,
        'max_retries' => 3,
        'enable_logging' => true,
        'log_level' => 'info',
        'auto_cleanup_logs' => true,
        'log_retention_days' => 30,
    ],
    'source_settings' => [
        'woocommerce_points_and_rewards' => [
            'enabled' => true,
            'table_prefix' => 'wc_points_rewards_',
            'mapping' => [
                'points' => 'points',
                'user_id' => 'user_id',
                'order_id' => 'order_id',
                'date' => 'date_earned'
            ]
        ],
        'yith_woocommerce_points_and_rewards' => [
            'enabled' => true,
            'table_prefix' => 'yith_ywpar_',
            'mapping' => [
                'points' => 'points',
                'user_id' => 'user_id',
                'order_id' => 'order_id',
                'date' => 'date_earned'
            ]
        ],
        'woocommerce_points_rewards' => [
            'enabled' => true,
            'table_prefix' => 'wc_points_rewards_',
            'mapping' => [
                'points' => 'points',
                'user_id' => 'user_id',
                'order_id' => 'order_id',
                'date' => 'date_earned'
            ]
        ],
        'custom' => [
            'enabled' => false,
            'table_prefix' => '',
            'mapping' => []
        ]
    ],
    'notification_settings' => [
        'admin_notifications' => [
            'migration_started' => true,
            'migration_completed' => true,
            'migration_failed' => true,
            'migration_progress' => false,
        ],
        'email_settings' => [
            'admin_email' => get_option('admin_email'),
            'notification_email' => get_option('admin_email'),
            'enable_email_notifications' => true,
        ]
    ],
    'performance_settings' => [
        'memory_limit' => '256M',
        'max_execution_time' => 300,
        'enable_background_processing' => false,
        'chunk_size' => 1000,
        'delay_between_chunks' => 1,
    ],
    'security_settings' => [
        'require_authentication' => true,
        'allowed_user_roles' => ['administrator', 'shop_manager'],
        'enable_csrf_protection' => true,
        'max_concurrent_migrations' => 1,
    ],
    'backup_settings' => [
        'enable_backup' => true,
        'backup_before_migration' => true,
        'backup_after_migration' => false,
        'backup_retention' => 7,
        'backup_location' => 'database',
    ]
];
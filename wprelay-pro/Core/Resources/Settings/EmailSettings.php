<?php

namespace RelayWp\Affiliate\Core\Resources\Settings;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Resource;

class EmailSettings extends Resource
{

    public function toArray($settings)
    {
        return [
            'affiliate_emails' => [
                'affiliate_approved' => (bool)($settings['affiliate_emails']['affiliate_approved'] ?? false),
                'affiliate_rejected' => (bool)($settings['affiliate_emails']['affiliate_rejected'] ?? false),
                'commission_approved' => (bool)($settings['affiliate_emails']['commission_approved'] ?? false),
                'commission_rejected' => (bool)($settings['affiliate_emails']['commission_rejected'] ?? false),
                'affiliate_new_sale_made' => (bool)($settings['affiliate_emails']['affiliate_new_sale_made'] ?? false),
                'payment_processed' => (bool)($settings['affiliate_emails']['payment_processed'] ?? false),
            ],
            'admin_emails' => [
                'affiliate_registered' => (bool)($settings['admin_emails']['affiliate_registered'] ?? false),
                'affiliate_sale_made' => (bool)($settings['admin_emails']['affiliate_sale_made'] ?? false),
            ],
        ];
    }
}


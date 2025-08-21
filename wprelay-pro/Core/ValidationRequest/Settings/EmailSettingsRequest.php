<?php

namespace RelayWp\Affiliate\Core\ValidationRequest\Settings;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;

class EmailSettingsRequest implements FormRequest
{
    public function rules(Request $request)
    {
        $rules = [
            'affiliate_emails' => ['required', 'array'],
            'affiliate_emails.affiliate_approved' => ['optional', 'bool_type'],
            'affiliate_emails.affiliate_rejected' => ['optional', 'bool_type'],
            'affiliate_emails.commission_approved' => ['optional', 'bool_type'],
            'affiliate_emails.commission_rejected' => ['optional', 'bool_type'],
            'affiliate_emails.payment_processed' => ['optional', 'bool_type'],
            'admin_emails' => ['required', 'array'],
            'admin_emails.affiliate_registered' => ['optional', 'bool_type'],
            'admin_emails.affiliate_sale_made' => ['optional', 'bool_type'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [];
    }
}

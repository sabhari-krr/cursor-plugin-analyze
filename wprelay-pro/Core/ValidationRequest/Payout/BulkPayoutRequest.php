<?php

namespace RelayWp\Affiliate\Core\ValidationRequest\Payout;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;

class BulkPayoutRequest implements FormRequest
{
    public function rules(Request $request)
    {
        $rules = [
            'selected_payments' => ['required', 'array'],
            'payment_source' => ['required', 'string_rwt'],
            'affiliate_notes' => ['required'],
            'admin_notes' => ['required'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [];
    }
}


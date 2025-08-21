<?php

namespace RelayWp\Affiliate\Core\ValidationRequest\Payout;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;

class PayoutRequest implements FormRequest
{
    public function rules(Request $request)
    {
        $rules = [
            'affiliate_id' => ['required', 'numeric'],
            'amount_to_pay' => ['required', 'numeric'],
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


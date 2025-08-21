<?php

namespace RelayWp\Affiliate\Core\ValidationRequest\Payout;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;

class DeletePayoutRequest implements FormRequest
{
    public function rules(Request $request)
    {
        $rules = [
            'payout_id' => ['required', 'numeric'],
            'revert_reason' => ['required'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [];
    }
}


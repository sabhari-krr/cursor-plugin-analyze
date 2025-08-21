<?php

namespace RelayWp\Affiliate\Pro\ValidationRequest\Rules;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;

class RuleUpdateStatusRequest implements FormRequest
{
    public function rules(Request $request)
    {
        $rules = [
            'rule_id' => ['required', 'numeric'],
            'status' => ['required']
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [];
    }
}


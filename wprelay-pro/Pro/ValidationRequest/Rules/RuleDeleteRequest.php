<?php

namespace RelayWp\Affiliate\Pro\ValidationRequest\Rules;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;

class RuleDeleteRequest implements FormRequest
{
    public function rules(Request $request)
    {
        $rules = [
            'rule_id' => ['required', 'numeric']
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [];
    }
}


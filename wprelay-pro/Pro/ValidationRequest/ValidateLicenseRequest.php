<?php

namespace RelayWp\Affiliate\Pro\ValidationRequest;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;

class ValidateLicenseRequest implements FormRequest
{
    public function rules(Request $request)
    {
        $rules = [
            'licence_key' => ['required']
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [];
    }
}


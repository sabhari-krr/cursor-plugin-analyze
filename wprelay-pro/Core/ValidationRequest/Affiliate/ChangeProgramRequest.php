<?php

namespace RelayWp\Affiliate\Core\ValidationRequest\Affiliate;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;
use RelayWp\Affiliate\Core\Models\Affiliate;
use RelayWp\Affiliate\Core\Models\Member;

class ChangeProgramRequest implements FormRequest
{
    public function rules(Request $request)
    {
        $rules = [
            'chosen_program_id' => ['required', 'integer'],
            'affiliate_id' => ['required', 'integer']
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [];
    }
}


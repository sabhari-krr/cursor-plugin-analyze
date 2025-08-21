<?php

namespace RelayWp\Affiliate\Core\ValidationRequest\Program;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;
use RelayWp\Affiliate\Core\Models\Program;

class ProgramIDRequest implements FormRequest
{
    public function rules(Request $request)
    {
        $rules = [
            'program_id' => ['required'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [];
    }
}


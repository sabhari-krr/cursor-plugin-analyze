<?php

namespace RelayWp\Affiliate\Core\ValidationRequest\Program;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;

class ProgramStatusRequest implements FormRequest
{

    public function rules(Request $request)
    {
        return [
            'program_id' => ['required', 'numeric'],
            'status' => ['required', ['in', ['draft', 'active', 'archived']]]
        ];
    }

    public function messages(): array
    {
        return [];
    }
}


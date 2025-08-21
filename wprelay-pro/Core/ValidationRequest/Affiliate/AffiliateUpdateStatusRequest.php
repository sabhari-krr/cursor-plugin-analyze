<?php

namespace RelayWp\Affiliate\Core\ValidationRequest\Affiliate;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;
use RelayWp\Affiliate\Core\Models\Affiliate;
use RelayWp\Affiliate\Core\Models\Member;

class AffiliateUpdateStatusRequest implements FormRequest
{
    public function rules(Request $request)
    {
        $data = $request->all();


        $rules = [
            'affiliate_id' => ['required', 'numeric'],
            'status' => ['required', 'string_rwt'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [];
    }
}

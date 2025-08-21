<?php

namespace RelayWp\Affiliate\Core\ValidationRequest\Affiliate;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\WC;
use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;
use RelayWp\Affiliate\Core\Models\Affiliate;
use RelayWp\Affiliate\Core\Models\Member;

class AffiliateUpdateCodeRequest implements FormRequest
{
    public function rules(Request $request)
    {
        $rules = [
            'referral_code' => ['required', 'string_rwt'],
            'affiliate_id' => ['required', 'integer']
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [];
    }
}


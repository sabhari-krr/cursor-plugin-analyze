<?php

namespace RelayWp\Affiliate\Core\Resources\Affiliate;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\PluginHelper;
use RelayWp\Affiliate\App\Helpers\WC;
use RelayWp\Affiliate\App\Resource;
use RelayWp\Affiliate\Core\Models\Affiliate;
use RelayWp\Affiliate\Core\Models\Program;

class AffiliateProfileResource extends Resource
{
    public function toArray($affiliate, $member, $program, $pending_payment_count = 0)
    {
        $shippingAddress = Functions::jsonDecode($affiliate->shipping_address ?? null);

        return [
            'affiliate' => [
                'affiliate_id' => $affiliate->id,
                'member_id' => $member->id,
                'status' => $affiliate->status,
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'url' => Affiliate::getReferralCodeURL($affiliate),
                'code' => $affiliate->referral_code,
                'email' => $member->email,
                'phone_number' => $affiliate->phone_number,
                'billing_email' => $affiliate->payment_email,
                "shipping_address" => Affiliate::getShippingAddressDetails($affiliate->shipping_address),
                'tags' => Functions::jsonDecode($affiliate->tags) ?? [],
                "social_links" => Affiliate::getSocialLinks($affiliate->social_links),
                'created_at' => Functions::getWcTime($affiliate->created_at),
                'updated_at' => Functions::getWcTime($affiliate->updated_at),
                'is_program_created' => (bool)$affiliate->program_id,
                'program_id' => $affiliate->program_id,
                'is_woocommerce_account_created' => $affiliate->wp_customer_id > 0 || email_exists($member->email),
                'is_expired' => !Program::isValid($program),
                'pending_payment_count' => (int)$pending_payment_count,
                'paypal_connected' => (bool)$affiliate->payment_email,
                'meta_fields' => Affiliate::getAffiliateMetaData($affiliate->meta_data)

            ],
            'program' => !empty($program) ? [
                'name' => $program->title,
                'program_id' => $program->id,
            ] : null
        ];
    }
}


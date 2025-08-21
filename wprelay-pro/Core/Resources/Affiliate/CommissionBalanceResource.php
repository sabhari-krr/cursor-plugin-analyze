<?php

namespace RelayWp\Affiliate\Core\Resources\Affiliate;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\WC;
use RelayWp\Affiliate\App\Resource;

class CommissionBalanceResource extends Resource
{

    public function toArray($affiliate, $member, $commissionBalance, $currency, $pendingPaymentCount)
    {
        return
            [
                'commission_balance' => [
                    'commission_balance_amount' => $commissionBalance,
                    'commission_balance_currency' => WC::getWcCurrencySymbol($currency),
                    'commission_balance_formatted_amount' => Functions::formatAmount($commissionBalance, $currency),
                ],
                'affiliate' => [
                    'email' => $member->email,
                    'affiliate_id' => $affiliate->id,
                    'pending_payment_count' => (int)$pendingPaymentCount
                ]
            ];
    }
}


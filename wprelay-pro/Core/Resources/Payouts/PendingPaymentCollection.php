<?php

namespace RelayWp\Affiliate\Core\Resources\Payouts;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\WC;
use RelayWp\Affiliate\Core\Models\Affiliate;
use RelayWp\Affiliate\App\Collection;

class PendingPaymentCollection extends Collection
{
    public function toArray($items, $totalCount, $perPage, $currentPage)
    {
        $data = [];
        foreach ($items as $item) {
            $data[] = [
                'payment_details' => [
                    'payment_source' => ucfirst('Manual'),
                ],
                'commission_balance' => [
                    'commission_balance_amount' => $item->balance,
                    'commission_balance_currency' => WC::getWcCurrencySymbol($item->currency),
                    'commission_balance_formatted_amount' => Functions::formatAmount($item->balance, $item->currency),
                ],
                'affiliate' => [
                    'email' => $item->affiliate_email,
                    'paypal_billing_email' => $item->paypal_billing_email ? $item->paypal_billing_email : null,
                    'paypal_connected' => (bool)$item->paypal_billing_email,
                    'pending_payout_count' => (int)$item->pending_payout_count ?? 0,
                    'affiliate_id' => $item->affiliate_id,
                    'name' => $item->affiliate_first_name . ' ' . $item->affiliate_last_name,
                ]
            ];
        }
        return [
            "total" => $totalCount,
            "per_page" => $perPage,
            "total_pages" => ceil($totalCount / $perPage),
            "current_page" => $currentPage,
            'pending_payments' => $data
        ];
    }
}


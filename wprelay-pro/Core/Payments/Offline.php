<?php

namespace RelayWp\Affiliate\Core\Payments;

defined('ABSPATH') or exit;

use RelayWp\Affiliate\Core\Models\Payout;
use RelayWp\Affiliate\Core\Models\Transaction;

class Offline extends RWPPayment
{
    protected $payout = null;

    public static function addOfflinePayment($paymentMethods)
    {
        $paymentMethods['manual'] = new self();

        return $paymentMethods;
    }

    public function getPaymentSource()
    {
        return [
            'name' => __('Manual', 'relay-affiliate-marketing'),
            'value' => 'manual',
            'label' => __('Offline Payment', 'relay-affiliate-marketing'),
            'description' => __('Offline payment methods are ways to pay, that do not require a credit card or a traditional payment gateway', 'relay-affiliate-marketing'),
            'disabled' => true
        ];
    }

    /**
     * @param $payout
     * @return void
     */
    public function process($payout_ids)
    {
        foreach ($payout_ids as $payout_id) {
            true ? $this->paymentSucceeded($payout_id, []) : $this->paymentFailed($payout_id, []);
        }
    }
}

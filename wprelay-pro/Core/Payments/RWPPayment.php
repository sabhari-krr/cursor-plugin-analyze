<?php

namespace RelayWp\Affiliate\Core\Payments;

defined('ABSPATH') or exit;

use RelayWp\Affiliate\Core\Models\Payout;

abstract class RWPPayment
{
    public abstract function getPaymentSource();

    public abstract function process($payout);

    /**
     * action rwpa_record_rwt_payment
     * @param $payout_ids
     * @param $paymentSource
     * @return void
     */
    public static function processPayment($payout_ids, $paymentSource)
    {
        if (empty($payout_ids))
            return;

        $paymentObjects = apply_filters('rwpa_payment_process_sources', []);

        foreach ($paymentObjects as $key => $object) {
            if ($key == $paymentSource) {
                $paymentObject = $object;
            }
        }

        if (isset($paymentObject)) { // need to check
            $paymentObject->process($payout_ids);
        } else {
            foreach ($payout_ids as $id) {
                do_action('rwpa_payment_mark_as_failed', $id, [
                    'message' => 'Payment object not found to process'
                ]);
            }
        }
    }

    public static function getPaymentSources($data = [])
    {
        $paymentObjects = apply_filters('rwpa_payment_process_sources', []);

        $sources = [];

        foreach ($paymentObjects as $key => $object) {
            $sources[] = $object->getPaymentSource();
        }

        return $sources;
    }

    public function paymentSucceeded($payout_id, $additional = [])
    {
        do_action('rwpa_payment_mark_as_succeeded', $payout_id, $additional);
    }

    public function paymentFailed($payout_id, $additional = [])
    {
        do_action('rwpa_payment_mark_as_failed', $payout_id, $additional);
    }
}

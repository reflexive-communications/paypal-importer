<?php

namespace Civi\PaypalImporter;

use Civi\RcBase\ApiWrapper\Get;

class Transformer
{
    /**
     * Mapping of PayPal transaction status to Civi contribution status
     */
    public const CONTRIBUTION_STATUS_MAP = [
        'D' => 'Failed',
        'F' => 'Refunded',
        'P' => 'Pending',
        'S' => 'Completed',
        'V' => 'Refunded',
    ];

    /**
     * Transform paypal transaction data to civicrm contact data
     *
     * @param array $transaction paypal transaction object
     *
     * @return array ContactData
     */
    public static function paypalTransactionToContact(array $transaction): array
    {
        return [
            'contact_type' => 'Individual',
            'first_name' => $transaction['payer_info']['payer_name']['given_name'] ?? '',
            'last_name' => $transaction['payer_info']['payer_name']['surname'] ?? '',
        ];
    }

    /**
     * Transform paypal transaction data to civicrm email data
     *
     * @param array $transaction paypal transaction object
     *
     * @return array EmailData
     * @throws \Civi\RcBase\Exception\APIException
     */
    public static function paypalTransactionToEmail(array $transaction): array
    {
        return [
            'location_type_id' => Get::defaultLocationTypeID() ?? 1,
            'email' => $transaction['payer_info']['email_address'] ?? '',
        ];
    }

    /**
     * Transform paypal transaction data to civicrm contribution data
     * For the civicrm the fee_amount has to be multiplied by -1.
     *
     * @param array $transaction paypal transaction object
     *
     * @return array ContributionData
     */
    public static function paypalTransactionToContribution(array $transaction): array
    {
        $contributionData = [
            'total_amount' => $transaction['transaction_info']['transaction_amount']['value'],
            'fee_amount' => intval($transaction['transaction_info']['fee_amount']['value']) * -1,
            'non_deductible_amount' => $transaction['transaction_info']['transaction_amount']['value'],
            'trxn_id' => $transaction['transaction_info']['transaction_id'],
            'receive_date' => $transaction['transaction_info']['transaction_initiation_date'],
            'invoice_number' => $transaction['transaction_info']['invoice_id'] ?? '',
            'source' => $transaction['cart_info']['item_details'][0]['item_name'] ?? '',
            'contribution_status_id:name' => self::CONTRIBUTION_STATUS_MAP[$transaction['transaction_info']['transaction_status']] ?? '',
        ];
        // setup contribution_cancel_date to transaction_updated_date if the contribution status is Refunded
        if ($contributionData['contribution_status_id:name'] == 'Refunded') {
            $contributionData['contribution_cancel_date'] = $transaction['transaction_info']['transaction_updated_date'];
        }

        return $contributionData;
    }
}

<?php

namespace Civi\PaypalImporter;

use CRM_RcBase_Api_Get;

class Transformer
{
    public const CRM_FAILED_STATUS_ID = 4;

    public const CRM_REFUNDED_STATUS_ID = 7;

    public const CRM_PENDING_STATUS_ID = 2;

    public const CRM_COMPLETED_STATUS_ID = 1;

    /**
     * Transform paypal transaction data to civicrm contact data
     *
     * @param array $transaction paypal transaction object
     *
     * @return array ContactData
     */
    public static function paypalTransactionToContact(array $transaction): array
    {
        $contactData = [
            'contact_type' => 'Individual',
            'first_name' => '',
            'last_name' => '',
        ];
        if (isset($transaction['payer_info']['payer_name'])) {
            $payer = $transaction['payer_info']['payer_name'];
            $contactData['first_name'] = $payer['given_name'] ?? '';
            $contactData['last_name'] = $payer['surname'] ?? '';
        }

        return $contactData;
    }

    /**
     * Transform paypal transaction data to civicrm email data
     *
     * @param array $transaction paypal transaction object
     *
     * @return array EmailData
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public static function paypalTransactionToEmail(array $transaction): array
    {
        $emailData = [
            'location_type_id' => CRM_RcBase_Api_Get::defaultLocationTypeID() ?? 1,
        ];
        if (isset($transaction['payer_info'])) {
            $emailData['email'] = $transaction['payer_info']['email_address'] ?? '';
        }

        return $emailData;
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
            'fee_amount' => intval($transaction['transaction_info']['fee_amount']['value'], 10) * -1,
            'non_deductible_amount' => $transaction['transaction_info']['transaction_amount']['value'],
            'trxn_id' => $transaction['transaction_info']['transaction_id'],
            'receive_date' => $transaction['transaction_info']['transaction_initiation_date'],
            'invoice_number' => $transaction['transaction_info']['invoice_id'] ?? '',
            'source' => $transaction['cart_info']['item_details'][0]['item_name'] ?? '',
            'contribution_status_id' => self::paypalTransactionStatusToCivicrmContributionStatus($transaction['transaction_info']['transaction_status']),
        ];
        // setup contribution_cancel_date to transaction_updated_date if the contribution status is Refunded
        if ($contributionData['contribution_status_id'] === self::CRM_REFUNDED_STATUS_ID) {
            $contributionData['contribution_cancel_date'] = $transaction['transaction_info']['transaction_updated_date'];
        }

        return $contributionData;
    }

    /**
     * Map paypal transaction status to civicrm contribution status.
     *
     * @param string $status paypal transaction status
     *
     * @return int civicrm contribution status id
     */
    private static function paypalTransactionStatusToCivicrmContributionStatus(string $status): int
    {
        $statusMapping = [
            // Failed status
            'D' => self::CRM_FAILED_STATUS_ID,
            // Refunded status
            'F' => self::CRM_REFUNDED_STATUS_ID,
            // Pending status
            'P' => self::CRM_PENDING_STATUS_ID,
            // Completed status
            'S' => self::CRM_COMPLETED_STATUS_ID,
            // Refunded status
            'V' => self::CRM_REFUNDED_STATUS_ID,
        ];
        if (array_key_exists($status, $statusMapping)) {
            return $statusMapping[$status];
        }

        return 0;
    }
}

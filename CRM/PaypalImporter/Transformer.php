<?php

class CRM_PaypalImporter_Transformer
{
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
            $contactData['first_name'] = $payer['given_name'] ?: '';
            $contactData['last_name'] = $payer['surname'] ?: '';
        }

        return $contactData;
    }

    /**
     * Transform paypal transaction data to civicrm email data
     *
     * @param array $transaction paypal transaction object
     *
     * @return array EmailData
     */
    public static function paypalTransactionToEmail(array $transaction): array
    {
        $emailData = [
            'location_type_id' => CRM_RcBase_Api_Get::defaultLocationTypeID() ?? 1,
        ];
        if (isset($transaction['payer_info'])) {
            $emailData['email'] = $transaction['payer_info']['email_address'] ?: '';
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
            'fee_amount' => intval($transaction['transaction_info']['fee_amount']['value'], 10)*-1,
            'non_deductible_amount' => $transaction['transaction_info']['transaction_amount']['value'],
            'trxn_id' => $transaction['transaction_info']['transaction_id'],
            'receive_date' => $transaction['transaction_info']['transaction_initiation_date'],
            'invoice_number' => $transaction['transaction_info']['invoice_id'],
            'source' => $transaction['cart_info']['item_details'][0]['item_name'] ?: '' ,
        ];

        return $contributionData;
    }
}

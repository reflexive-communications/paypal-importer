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
}

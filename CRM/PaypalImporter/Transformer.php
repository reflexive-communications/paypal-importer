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
}

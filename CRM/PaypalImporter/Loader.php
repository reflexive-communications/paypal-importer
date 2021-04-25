<?php

use Civi\Api4\Contribution;

class CRM_PaypalImporter_Loader
{
    /**
     * Import new contact
     *
     * @param array $contactData Contact data
     *
     * @return int Contact ID
     *
     * @throws CRM_Core_Exception
     */
    public static function contact(array $contactData): int
    {
        return CRM_RcBase_Api_Create::contact($contactData, false);
    }

    /**
     * Import new email
     *
     * @param int Contact ID
     * @param array $emailData email data
     *
     * @return int Email ID
     *
     * @throws CRM_Core_Exception
     */
    public static function email(int $contactId, array $emailData): int
    {
        return CRM_RcBase_Api_Create::email($contactId, $emailData, false);
    }

    /**
     * Import new contribution
     *
     * @param int Contact ID
     * @param array $contributionData contribution data
     *
     * @return int Contribution ID
     *
     * @throws CRM_Core_Exception
     */
    public static function contribution(int $contactId, array $contributionData): int
    {
        $contributions = Contribution::get(false)
            ->addSelect('contribution_id')
            ->addWhere('trxn_id', '=', $contributionData['trxn_id'])
            ->setLimit(1)
            ->execute();
        // Get first result row
        $contribution = $contributions->first();
        if (!is_array($contribution)) {
            // Question: What should we do if the transaction is active in paypal, but refunded in civi?
            return CRM_RcBase_Api_Create::contribution($contactId, $contributionData, false);
        } else {
            CRM_RcBase_Api_Update::entity('Contribution', $contribution['id'], $contributionData, false);
            return $contribution['id'];
        }
    }
}

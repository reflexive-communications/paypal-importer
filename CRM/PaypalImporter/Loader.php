<?php

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
        return CRM_RcBase_Api_Create::contribution($contactId, $contributionData, false);
    }
}

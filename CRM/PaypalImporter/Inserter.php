<?php

class CRM_PaypalImporter_Inserter
{
    /**
     * Import new contact
     *
     * @param array $values Contact data
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
     * @param array $values email data
     *
     * @return int Email ID
     *
     * @throws CRM_Core_Exception
     */
    public static function email(int $contactId, array $emailData): int
    {
        return CRM_RcBase_Api_Create::email($contactId, $emailData, false);
    }
}

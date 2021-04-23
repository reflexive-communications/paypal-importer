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
}

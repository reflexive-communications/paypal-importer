<?php

namespace Civi\PaypalImporter;

use Civi\RcBase\ApiWrapper\Create;
use Civi\RcBase\ApiWrapper\Get;
use Civi\RcBase\ApiWrapper\Update;
use CRM_Core_Exception;

class Loader
{
    /**
     * Import new contact
     *
     * @param array $contactData Contact data
     *
     * @return int Contact ID
     * @throws CRM_Core_Exception
     */
    public static function contact(array $contactData): int
    {
        return Create::contact($contactData);
    }

    /**
     * Import new email
     *
     * @param int $contactId Contact ID
     * @param array $emailData email data
     *
     * @return int Email ID
     * @throws CRM_Core_Exception
     */
    public static function email(int $contactId, array $emailData): int
    {
        return Create::email($contactId, $emailData);
    }

    /**
     * Import new contribution
     *
     * @param int $contactId Contact ID
     * @param array $contributionData contribution data
     *
     * @return int Contribution ID
     * @throws CRM_Core_Exception
     */
    public static function contribution(int $contactId, array $contributionData): int
    {
        $contribution_id = Get::contributionIDByTransactionID($contributionData['trxn_id']);
        if (is_null($contribution_id)) {
            // Question: What should we do if the transaction is active in paypal, but refunded in civi?
            return Create::contribution($contactId, $contributionData);
        } else {
            Update::contribution($contribution_id, $contributionData);

            return $contribution_id;
        }
    }
}

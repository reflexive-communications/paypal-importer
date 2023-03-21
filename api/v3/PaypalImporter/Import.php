<?php

use CRM_PaypalImporter_ExtensionUtil as E;

/**
 * PaypalImporter.Import API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @throws API_Exception
 * @see civicrm_api3_create_success
 */
function civicrm_api3_paypal_importer_Import($params): array
{
    $p = new CRM_PaypalImporter_ImportProcess(E::LONG_NAME, CRM_PaypalImporter_Request_Auth::class, CRM_PaypalImporter_Request_Transactions::class);

    return $p->run($params);
}

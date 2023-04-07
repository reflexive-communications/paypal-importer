<?php

use Civi\PaypalImporter\ImportProcess;
use Civi\PaypalImporter\Request\Auth;
use Civi\PaypalImporter\Request\Transactions;
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
    $p = new ImportProcess(E::LONG_NAME, Auth::class, Transactions::class);

    return $p->run($params);
}

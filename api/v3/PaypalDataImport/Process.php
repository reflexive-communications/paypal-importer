<?php
use CRM_PaypalImporter_ExtensionUtil as E;

/**
 * PaypalDataImport.Process API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_paypal_data_import_Process_spec(&$spec)
{
}

/**
 * PaypalDataImport.Process API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_paypal_data_import_Process($params)
{
    $p = new CRM_PaypalImporter_ImportProcess(E::LONG_NAME, CRM_PaypalImporter_Request_Auth::class, CRM_PaypalImporter_Request_Transactions::class);
    return $p->run($params);
}

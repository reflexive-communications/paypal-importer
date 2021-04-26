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
    // Load configuration
    $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
    $config->load();
    $cfg = $config->get();

    // Check the config. if the state is do-nothing or error, return.
    if ($cfg['state'] == 'do-nothing' || $cfg['state'] == 'error') {
        return civicrm_api3_create_success(['state' => $cfg['state']], $params, 'PaypalDataImport', 'Process');
    }

    // On case of started import, the status params needs to be initialized
    if ($cfg['state'] == 'import-init') {
        $config->updateState('import');
        $importParams = [
            'page' => 1,
            'start-date' => $cfg['settings']['start-date'],
        ];
        $config->updateImportParams($importParams);
        $cfg = $config->get();
    }

    // Authenticate - get access token
    $authData = authenticate($cfg['settings']);

    // Setup search parameters
    $transactionSearchParams = [
        'page_size' => $cfg['settings']['import-limit'],
        'page' => $cfg['import-params']['page'],
        'start_date' => date(DATE_ISO8601, strtotime($cfg['import-params']['start-date'])),
        'end_date' => date(DATE_ISO8601, strtotime($cfg['import-params']['start-date'] . ' +30 days')),
        'fields' => 'transaction_info,payer_info,cart_info',
    ];
    // loop until the next search start date is greater than now. or we reached the request limit.
    $numberOfRequests = 0;
    $stats = [
        'new-user' => 0,
        'transaction' => 0,
        'errors' => [],
    ];
    while ($numberOfRequests < $cfg['settings']['request-limit']) {
        $transactionReq = new CRM_PaypalImporter_Request_Transactions($cfg['settings']['paypal-host'], $authData['access_token'], $transactionSearchParams);
        $transactionReq->get();
        $transactionResponse = $transactionReq->getResponse();
        // If the transaction search failed, set error state.
        if (intval($transactionResponse['code']) !== 200) {
            $config->updateState('error');
            throw new API_Exception('Paypal transaction search failure', 'paypal_transaction_search_failure');
        }
        $transactionData = json_decode($transactionResponse['data'], true);
        foreach ($transactionData['transaction_details'] as $transaction) {
            $currentStat = processTransaction($cfg['settings'], $transaction);
            $stats['new-user'] += $currentStat['new-user'];
            $stats['transaction'] += $currentStat['transaction'];
            $stats['errors'] = array_merge($stats['errors'], $currentStat['errors']);
        }
        // if we need paging (transaction total_pages > import-params.page), page increase, save import params.
        // If we are on the last page, we increase the start date and end date and set the page to 1 for the next api call.
        if ($transactionData['total_pages'] > $cfg['import-params']['page']) {
            $importParams = [
                'page' => $cfg['import-params']['page'] + 1,
                'start-date' => $cfg['import-params']['start-date'],
            ];
            $config->updateImportParams($importParams);
            $cfg = $config->get();
            $transactionSearchParams['page'] = $cfg['import-params']['page'];
        } elseif ($transactionData['total_pages'] == $cfg['import-params']['page']) {
            // - On case of the end date of the current one is greater than now, set the start date (db import config) to the "last_refreshed_datetime": "2017-01-02T06:59:59+0000",
            // value. Set the state to sync, break loop.
            if ($transactionSearchParams['end_date'] > date(DATE_ISO8601, strtotime('now'))) {
                $importParams = [
                    'page' => 1,
                    'start-date' => date('Y-m-d H:i', strtotime($transactionData['last_refreshed_datetime'])),
                ];
                $config->updateState('sync');
                $config->updateImportParams($importParams);
                break;
            }
            // - Else set start date to end date, calculate new end date, set page to 0, save start-date, page db configs, return loop.
            // increase the number of requests.
            $importParams = [
                'page' => 1,
                'start-date' => date('Y-m-d H:i', strtotime($transactionSearchParams['end_date'])),
            ];
            $config->updateImportParams($importParams);
            $cfg = $config->get();
            $transactionSearchParams['start_date'] = $transactionSearchParams['end_date'];
            $transactionSearchParams['end_date'] = date(DATE_ISO8601, strtotime($importParams['start-date'] . ' +30 days'));
            $transactionSearchParams['page'] = $importParams['page'];
        }
        $numberOfRequests += 1;
    }
    return civicrm_api3_create_success(['stats' => $stats], $params, 'PaypalDataImport', 'Process');
}

/**
 * authenticate function.
 *
 * @param array $cfg the settigs configuration
 *
 * @return array the received authentication data
 *
 * @throws API_Exception
 */
function authenticate(array $cfg): array
{
    // Authenticate - get access token
    $authReq = new CRM_PaypalImporter_Request_Auth($cfg['paypal-host'], $cfg['client-id'], $cfg['client-secret']);
    $authReq->post();
    $authResponse = $authReq->getResponse();
    $authData = json_decode($authResponse['data'], true);
    // check response code. if not 200 or the token is missing from the response,
    // push state to error, add log: authentication failed.
    if (intval($authResponse['code']) !== 200 || empty($authData['access_token'])) {
        $cfg->updateState('error');
        throw new API_Exception('Paypal authentication failure', 'paypal_auth_failure');
    }
    return $authData;
}

/**
 * Process one transaction.
 *
 * @param array $cfg the settigs configuration
 * @param array $transaction the paypal transaction
 *
 * @return array stats of the current iteration
 *
 * @throws API_Exception
 */
function processTransaction(array $cfg, array $transaction): array
{
    $stats = [
        'new-user' => 0,
        'transaction' => 0,
        'errors' => [],
    ];
    // Check email first. If missing, the process will be skipped.
    $emailData = CRM_PaypalImporter_Transformer::paypalTransactionToEmail($transaction);
    if (empty($emailData['email'])) {
        $stats['errors'][] = $transaction['transaction_info']['transaction_id'].' | Skipping transaction due to missing email address.';
        return $stats;
    }
    // Try to find a contact to the email. If not found, we have to insert a contact and also the email.
    $contactId = CRM_RcBase_Api_Get::contactIDFromEmail($emailData['email']);
    if (is_null($contactId)) {
        $contactData = CRM_PaypalImporter_Transformer::paypalTransactionToContact($transaction);
        try {
            $contactId = CRM_PaypalImporter_Loader::contact($contactData);
            CRM_PaypalImporter_Loader::email($contactId, $emailData);
            $stats['new-user'] = 1;
        } catch (Exception $e) {
            $stats['errors'][] =  $transaction['transaction_info']['transaction_id'].' | '.$e->getMessage();
        }
    }
    $contributionData = CRM_PaypalImporter_Transformer::paypalTransactionToContribution($transaction);
    $contributionData['financial_type_id'] = $cfg['financial-type-id'] ?: 1;
    $contributionData['payment_instrument_id'] = $cfg['payment-instrument-id'] ?: 'Credit Card';
    $contributionData['source'] = "paypal-importer-extension - " . $contributionData['source'];
    try {
        CRM_PaypalImporter_Loader::contribution($contactId, $contributionData);
        $stats['transaction'] = 1;
    } catch (Exception $e) {
        $stats['errors'][] =  $transaction['transaction_info']['transaction_id'].' | '.$e->getMessage();
    }
    return $stats;
}

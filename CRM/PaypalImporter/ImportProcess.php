<?php

class CRM_PaypalImporter_ImportProcess
{

    /**
     * @var CRM_PaypalImporter_Config config
     */
    private $config;
    /**
     * @var float executionStartTime
     */
    private $executionStartTime;
    /**
     * @var array authData
     */
    private $authData;
    /**
     * @var int numberOfRequests
     */
    private $numberOfRequests;
    /**
     * @var array stats
     */
    private $stats;
    /**
     * @var array searchParams
     */
    private $searchParams;

    /**
     * Default Constructor
     *
     * @param string $configName
     */
    public function __construct(string $configName)
    {
        $this->config = new CRM_PaypalImporter_Config($configName);
        $this->numberOfRequests = 0;
        $this->stats = [
            'new-user' => 0,
            'transaction' => 0,
            'errors' => [],
        ];
    }

    /**
     * It returns true if the import process has to be skipped.
     * If the state is do-nothing, it returns true.
     * If the state is error, it returns true.
     * For other states it returns false, so that the import process
     * will continue.
     *
     * @param string $state the current state from the config
     *
     * @return bool
     */
    private static function standByState(string $state): bool
    {
        return $state === 'do-nothing' || $state === 'error';
    }

    /**
     * It returns the current execution time.
     *
     * @return float the execution time
     */
    private function getExecutionTime(): float
    {
        return microtime(true) - $this->executionStartTime;
    }

    /**
     * If the import is just started (import-init)
     * we have to reset the import params configuration.
     */
    private function initImportParams(): void
    {
        $cfg = $this->config->get();
        if ($cfg['state'] === 'import-init') {
            $this->config->updateState('import');
            $importParams = [
                'page' => 1,
                'start-date' => $cfg['settings']['start-date'],
            ];
            $this->config->updateImportParams($importParams);
        }
    }

    /**
     * authenticate function.
     *
     * @throws API_Exception
     */
    private function authenticate()
    {
        $cfg = $this->config->get();
        // Authenticate - get access token
        $authReq = new CRM_PaypalImporter_Request_Auth($cfg['settings']['paypal-host'], $cfg['settings']['client-id'], $cfg['settings']['client-secret']);
        $authReq->post();
        $authResponse = $authReq->getResponse();
        $this->authData = json_decode($authResponse['data'], true);
        // check response code. if not 200 or the token is missing from the response,
        // push state to error, add log: authentication failed.
        if (intval($authResponse['code']) !== 200 || empty($this->authData['access_token'])) {
            $this->config->updateState('error');
            throw new API_Exception('Paypal authentication failure', 'paypal_auth_failure');
        }
    }

    /**
     * It builds the search params array from the current config values.
     */
    private function setupTransactionSearchParamsFromConfig(): void
    {
        $cfg = $this->config->get();
        $this->searchParams = [
            'page_size' => $cfg['settings']['import-limit'],
            'page' => $cfg['import-params']['page'],
            'start_date' => date(DATE_ISO8601, strtotime($cfg['import-params']['start-date'])),
            'end_date' => date(DATE_ISO8601, strtotime($cfg['import-params']['start-date'] . ' +30 days')),
            'fields' => 'transaction_info,payer_info,cart_info',
        ];
    }

    /**
     * It returns the resultset of the transaction search.
     *
     * @return array API result
     *
     * @throws API_Exception
     */
    private function getTransactionData(): array
    {
        $cfg = $this->config->get();
        $transactionReq = new CRM_PaypalImporter_Request_Transactions($cfg['settings']['paypal-host'], $this->authData['access_token'], $this->searchParams);
        $transactionReq->get();
        $transactionResponse = $transactionReq->getResponse();
        // If the transaction search failed, set error state.
        if (intval($transactionResponse['code']) !== 200) {
            $this->config->updateState('error');
            throw new API_Exception('Paypal transaction search failure', 'paypal_transaction_search_failure');
        }
        return json_decode($transactionResponse['data'], true);
    }

    /**
     * Process one transaction. First it checks the email. If the necessary data
     * is missing from the paypal transaction, it logs the error and returns.
     * Next it tries to find a contact with the given email. If not found a new
     * contact will be created and a new email record will be attached to it.
     * Finally the contribution will be created and attached to the user. Under the
     * hood it handles the contribution update also.
     *
     * @param array $transaction the paypal transaction
     *
     * @throws API_Exception
     */
    private function processTransaction(array $transaction): void
    {
        $stats = [
            'new-user' => 0,
            'transaction' => 0,
            'errors' => [],
        ];
        $cfg = $this->config->get();
        // Check email first. If missing, the process will be skipped.
        $emailData = CRM_PaypalImporter_Transformer::paypalTransactionToEmail($transaction);
        if (empty($emailData['email'])) {
            $this->stats['errors'][] = $transaction['transaction_info']['transaction_id'].' | Skipping transaction due to missing email address.';
            return;
        }
        // Try to find a contact to the email. If not found, we have to insert a contact and also the email.
        $contactId = CRM_RcBase_Api_Get::contactIDFromEmail($emailData['email']);
        if (is_null($contactId)) {
            $contactData = CRM_PaypalImporter_Transformer::paypalTransactionToContact($transaction);
            try {
                $contactId = CRM_PaypalImporter_Loader::contact($contactData);
                CRM_PaypalImporter_Loader::email($contactId, $emailData);
                $this->stats['new-user'] += 1;
            } catch (Exception $e) {
                $this->stats['errors'][] =  $transaction['transaction_info']['transaction_id'].' | '.$e->getMessage();
            }
        }
        $contributionData = CRM_PaypalImporter_Transformer::paypalTransactionToContribution($transaction);
        $contributionData['financial_type_id'] = $cfg['settings']['financial-type-id'];
        $contributionData['payment_instrument_id'] = $cfg['settings']['payment-instrument-id'];
        $contributionData['source'] = "paypal-importer-extension - " . $contributionData['source'];
        try {
            CRM_PaypalImporter_Loader::contribution($contactId, $contributionData);
            $this->stats['transaction'] += 1;
        } catch (Exception $e) {
            $this->stats['errors'][] =  $transaction['transaction_info']['transaction_id'].' | '.$e->getMessage();
        }
    }

    /**
     * It updates the searchParams config for processing a paging.
     *
     * @return int the new page.
     */
    private function pagingForTransactionSearch(): int
    {
        $cfg = $this->config->get();
        $importParams = [
            'page' => $cfg['import-params']['page'] + 1,
            'start-date' => $cfg['import-params']['start-date'],
        ];
        $this->config->updateImportParams($importParams);
        $cfg = $this->config->get();
        return $cfg['import-params']['page'];
    }

    /**
     * It sets the process state to sync. It is called after the 
     * import process is finished.
     *
     * @param string lastRefreshedDate.
     */
    private function pushToSyncState(string $lastRefreshedDate): void
    {
        $importParams = [
            'page' => 1,
            'start-date' => date('Y-m-d H:i', strtotime($lastRefreshedDate)),
        ];
        $this->config->updateState('sync');
        $this->config->updateImportParams($importParams);
    }

    /**
     * Updates the search params and the db config for the next import iteration. 
     */
    private function stepStartDate(): void
    {
        $importParams = [
            'page' => 1,
            'start-date' => date('Y-m-d H:i', strtotime($this->searchParams['end_date'])),
        ];
        $this->config->updateImportParams($importParams);
        $this->searchParams['start_date'] = $this->searchParams['end_date'];
        $this->searchParams['end_date'] = date(DATE_ISO8601, strtotime($importParams['start-date'] . ' +30 days'));
        $this->searchParams['page'] = $importParams['page'];
    }

    /**
     * It starts the import process.
     */
    public function run($params)
    {
        // For calculating the execution time that we insert to the stats.
        $this->executionStartTime = microtime(true);
        // purge last stats
        $this->config->updateImportStats([]);
        $this->config->load();
        $cfg = $this->config->get();

        // Check the config. if the state is do-nothing or error, return.
        if (self::standByState($cfg['state'])) {
            return civicrm_api3_create_success(['state' => $cfg['state'], 'stats' => ['execution-time' => $this->getExecutionTime()]], $params, 'PaypalDataImport', 'Process');
        }

        $this->initImportParams();

        // Authenticate - get access token
        $this->authenticate();
        $this->setupTransactionSearchParamsFromConfig();
        while ($this->numberOfRequests < $cfg['settings']['request-limit']) {
            $transactionData = $this->getTransactionData();
            foreach ($transactionData['transaction_details'] as $transaction) {
                $this->processTransaction($transaction);
            }
            // if we need paging (transaction total_pages > import-params.page), page increase, save import params.
            // If we are on the last page, we increase the start date and end date and set the page to 1 for the next api call.
            if ($transactionData['total_pages'] > $cfg['import-params']['page']) {
                $this->searchParams['page'] = $this->pagingForTransactionSearch();
            } elseif ($transactionData['total_pages'] == $cfg['import-params']['page']) {
                // - On case of the end date of the current one is greater than now, set the start date (db import config) to the "last_refreshed_datetime": "2017-01-02T06:59:59+0000",
                // value. Set the state to sync, break loop.
                if ($this->searchParams['end_date'] > date(DATE_ISO8601, strtotime('now'))) {
                    $this->pushToSyncState($transactionData['last_refreshed_datetime']);
                    break;
                }
                // - Else set start date to end date, calculate new end date, set page to 0, save start-date, page db configs, return loop.
                // increase the number of requests.
                $this->stepStartDate();
            }
            $this->numberOfRequests += 1;
        }
        $this->stats['execution-time'] = $this->getExecutionTime();
        $this->config->updateImportStats($this->stats);
        return civicrm_api3_create_success(['stats' => $this->stats], $params, 'PaypalDataImport', 'Process');
    }
}
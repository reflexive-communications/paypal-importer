<?php

namespace Civi\PaypalImporter;

use API_Exception;
use Civi;
use Civi\Api4\GroupContact;
use CRM_PaypalImporter_Upgrader;
use CRM_RcBase_Api_Create;
use CRM_RcBase_Api_Get;
use CRM_RcBase_Api_Save;
use Exception;

class ImportProcess
{
    /**
     * @var Config config
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
     * @var string authenticatorClass
     */
    private $authenticatorClass;

    /**
     * @var string authenticatorClass
     */
    private $transactionSearchClass;

    /**
     * Default Constructor
     *
     * @param string $configName
     * @param string $authenticatorClassName
     * @param string $transactionSearchClassName
     */
    public function __construct(string $configName, string $authenticatorClassName, string $transactionSearchClassName)
    {
        $this->config = new Config($configName);
        $this->numberOfRequests = 0;
        $this->stats = [
            'new-user' => 0,
            'transaction' => 0,
            'errors' => [],
        ];
        $this->authenticatorClass = $authenticatorClassName;
        $this->transactionSearchClass = $transactionSearchClassName;
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
     *
     * @throws \CRM_Core_Exception
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
    private function authenticate(): void
    {
        $cfg = $this->config->get();
        // Authenticate - get access token
        $authReq = new $this->authenticatorClass($cfg['settings']['paypal-host'], $cfg['settings']['client-id'], $cfg['settings']['client-secret']);
        $authReq->post();
        $authResponse = $authReq->getResponse();
        $this->authData = json_decode($authResponse['data'], true);
        // check response code. if not 200 or the token is missing from the response,
        // push state to error, add log: authentication failed.
        if (intval($authResponse['code']) !== 200 || empty($this->authData['access_token'])) {
            $this->config->updateState('error');
            $this->config->updateImportError('Paypal authentication failure');
            CRM_PaypalImporter_Upgrader::logError('Paypal authentication failure');
            throw new API_Exception('Paypal authentication failure', 'paypal_auth_failure');
        }
    }

    /**
     * It builds the search params array from the current config values.
     *
     * @throws \CRM_Core_Exception
     */
    private function setupTransactionSearchParamsFromConfig(): void
    {
        $cfg = $this->config->get();
        $this->searchParams = [
            'page_size' => $cfg['settings']['import-limit'],
            'page' => $cfg['import-params']['page'],
            'start_date' => date(DATE_ISO8601, strtotime($cfg['import-params']['start-date'])),
            'end_date' => date(DATE_ISO8601, strtotime($cfg['import-params']['start-date'].' +30 days')),
            'fields' => 'transaction_info,payer_info,cart_info',
        ];
    }

    /**
     * It returns the resultset of the transaction search.
     *
     * @return array API result
     * @throws API_Exception
     */
    private function getTransactionData(): array
    {
        $cfg = $this->config->get();
        $transactionReq = new $this->transactionSearchClass($cfg['settings']['paypal-host'], $this->authData['access_token'], $this->searchParams);
        $transactionReq->get();
        $transactionResponse = $transactionReq->getResponse();
        // If the transaction search failed, set error state.
        if (intval($transactionResponse['code']) !== 200) {
            $this->config->updateState('error');
            $this->config->updateImportError('Paypal transaction search failure');
            CRM_PaypalImporter_Upgrader::logError('Paypal transaction search failure');
            throw new API_Exception('Paypal transaction search failure', 'paypal_transaction_search_failure');
        }

        return json_decode($transactionResponse['data'], true);
    }

    /**
     * It extends the error stat with the given message
     * and prints the message to the file log as info.
     *
     * @param string $message the details of the issue
     */
    private function addInfo(string $message): void
    {
        $this->stats['errors'][] = $message;
        Civi::log()->info('Paypal-Importer | '.$message);
    }

    /**
     * It extends the error stat with the given message
     * and prints the message to the file log as error.
     *
     * @param string $message the details of the issue
     */
    private function addError(string $message): void
    {
        $this->stats['errors'][] = $message;
        CRM_PaypalImporter_Upgrader::logError($message);
    }

    /**
     * Process one transaction. First it checks the email. If the necessary data
     * is missing from the paypal transaction, it logs the error and returns.
     * Next it tries to find a contact with the given email. If not found a new
     * contact will be created and a new email record will be attached to it.
     * The tag and group fields are optional, but if they set, the contact will be tagged
     * and added to the group.
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
        $emailData = Transformer::paypalTransactionToEmail($transaction);
        if (empty($emailData['email'])) {
            $this->addInfo($transaction['transaction_info']['transaction_id'].' | Skipping transaction due to missing email address.');

            return;
        }
        // Try to find a contact to the email. If not found, we have to insert a contact and also the email.
        $contactId = CRM_RcBase_Api_Get::contactIDFromEmail($emailData['email']);
        if (is_null($contactId)) {
            $contactData = Transformer::paypalTransactionToContact($transaction);
            try {
                $contactId = Loader::contact($contactData);
                $this->stats['new-user'] += 1;
            } catch (Exception $e) {
                $this->addError(sprintf('%s (transaction_id: %s)', $e->getMessage(), $transaction['transaction_info']['transaction_id']));

                return;
            }
            try {
                Loader::email($contactId, $emailData);
            } catch (Exception $e) {
                $this->addError(sprintf('%s (transaction_id: %s)', $e->getMessage(), $transaction['transaction_info']['transaction_id']));
            }
        }
        // Add the tag to the user and also subscribe it to the group.
        if ($cfg['settings']['tag-id'] > 0) {
            try {
                CRM_RcBase_Api_Save::tagContact($contactId, $cfg['settings']['tag-id'], false);
            } catch (Exception $e) {
                $this->addError(sprintf('%s (transaction_id: %s)', $e->getMessage(), $transaction['transaction_info']['transaction_id']));
            }
        }
        if ($cfg['settings']['group-id'] > 0) {
            try {
                $this->groupContact($contactId, $cfg['settings']['group-id']);
            } catch (Exception $e) {
                $this->addError(sprintf('%s (transaction_id: %s)', $e->getMessage(), $transaction['transaction_info']['transaction_id']));
            }
        }
        $contributionData = Transformer::paypalTransactionToContribution($transaction);
        $contributionData['financial_type_id'] = $cfg['settings']['financial-type-id'];
        $contributionData['payment_instrument_id'] = $cfg['settings']['payment-instrument-id'];
        $contributionData['source'] = 'paypal-importer-extension - '.$contributionData['source'];
        try {
            Loader::contribution($contactId, $contributionData);
            $this->stats['transaction'] += 1;
        } catch (Exception $e) {
            $this->addError(sprintf('%s (transaction_id: %s)', $e->getMessage(), $transaction['transaction_info']['transaction_id']));
        }
    }

    /**
     * @param int $contactId
     * @param int $groupId
     *
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    private function groupContact(int $contactId, int $groupId): void
    {
        $result = GroupContact::get(false)
            ->addSelect('id')
            ->addWhere('contact_id', '=', $contactId)
            ->addWhere('group_id', '=', $groupId)
            ->setLimit(1)
            ->execute();
        // already in the group, skip insert step.
        if (is_array($result->first())) {
            return;
        }
        CRM_RcBase_Api_Create::entity('GroupContact', ['contact_id' => $contactId, 'group_id' => $groupId, 'status' => 'Added'], false);
    }

    /**
     * It updates the searchParams config for processing a paging.
     *
     * @return int the new page.
     * @throws \CRM_Core_Exception
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
     * @param string $lastRefreshedDate
     *
     * @throws \CRM_Core_Exception
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
        $this->searchParams['end_date'] = date(DATE_ISO8601, strtotime($importParams['start-date'].' +30 days'));
        $this->searchParams['page'] = $importParams['page'];
    }

    /**
     * It starts the import process.
     *
     * @param array $params
     *
     * @return array
     *   API result descriptor
     * @throws API_Exception
     * @see civicrm_api3_create_success
     */
    public function run($params): array
    {
        // For calculating the execution time that we insert to the stats.
        $this->executionStartTime = microtime(true);
        // purge last stats
        $this->config->updateImportStats([]);
        $this->config->load();
        $cfg = $this->config->get();

        // Check the config. if the state is do-nothing, return.
        if ($cfg['state'] == 'do-nothing') {
            return civicrm_api3_create_success(['state' => $cfg['state'], 'stats' => ['execution-time' => $this->getExecutionTime()]], $params, 'PaypalImporter', 'import');
        }

        $this->initImportParams();
        $this->config->load();
        $cfg = $this->config->get();

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
            } else {
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
        $this->stats['number-of-requests'] = $this->numberOfRequests;
        $this->config->updateImportStats($this->stats);
        $this->config->updateImportError('');

        return civicrm_api3_create_success(['stats' => $this->stats], $params, 'PaypalImporter', 'import');
    }
}

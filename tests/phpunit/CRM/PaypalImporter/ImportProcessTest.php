<?php

use CRM_PaypalImporter_ExtensionUtil as E;

/**
 * Import process test cases.
 *
 * @group headless
 */
class CRM_PaypalImporter_ImportProcessTest extends CRM_PaypalImporter_Request_TestBase
{
    // We don't use the params in the process script.
    const PARAMS = [];
    const TEST_SETTINGS = [
        'settings' => [
            'client-id' => '',
            'client-secret' => '',
            'paypal-host' => '',
            'start-date' => '',
            'import-limit' => 1,
            'financial-type-id' => '',
            'payment-instrument-id' => '',
            'request-limit' => 1,
        ],
        'state' => 'do-nothing',
        'import-params' => [
        ],
        'import-stats' => [
        ],
        'import-error' => '',
    ];
    private function setupTestConfig()
    {
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        self::assertTrue($config->update(self::TEST_SETTINGS), 'Config update has to be successful.');
    }

    /*
     * The run function is the only public, so that
     * the private function needs to be tested with
     * preset configs.
     */
    public function testRunStandByStates()
    {
        $this->setupTestConfig();
        $states = ['do-nothing', 'error'];
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        foreach ($states as $state) {
            $config->updateState($state);
            $p = new CRM_PaypalImporter_ImportProcess(E::LONG_NAME, 'fake_auth_class_name', 'fake_transactions_class_name');
            $result = $p->run(self::PARAMS);
            $config->load();
            $cfg = $config->get();
            self::assertSame([], $cfg['import-stats'], 'Invalid import stats. Supposed to be empty if the script is in stand-by mode.');
            self::assertTrue(array_key_exists('is_error', $result));
            self::assertSame(0, $result['is_error']);
            self::assertTrue(array_key_exists('values', $result));
            self::assertTrue(array_key_exists('state', $result['values']));
            self::assertSame($state, $result['values']['state']);
            self::assertTrue(array_key_exists('stats', $result['values']));
            self::assertTrue(array_key_exists('execution-time', $result['values']['stats']));
            self::assertIsFloat($result['values']['stats']['execution-time']);
        }
    }
    public function testRunFailedAuthWrongCode()
    {
        $this->setupTestConfig();
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        $config->updateState('import-init');
        $p = new CRM_PaypalImporter_ImportProcess(E::LONG_NAME, 'CRM_PaypalImporter_Request_AuthCodeMock', 'fake_transactions_class_name');
        self::expectException(API_Exception::class);
        $p->run(self::PARAMS);
    }
    public function testRunFailedAuthMissingToken()
    {
        $this->setupTestConfig();
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        $config->updateState('import-init');
        $p = new CRM_PaypalImporter_ImportProcess(E::LONG_NAME, 'CRM_PaypalImporter_Request_AuthTokenMock', 'fake_transactions_class_name');
        self::expectException(API_Exception::class);
        $p->run(self::PARAMS);
    }
    public function testRunSuccessfulAuth()
    {
        $this->setupTestConfig();
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        $config->updateState('import-init');
        // decrease request limit to 0, to prevent the transaction search call.
        $cfg = $config->get();
        $cfg['settings']['request-limit'] = 0;
        $config->updateSettings($cfg['settings']);
        $p = new CRM_PaypalImporter_ImportProcess(E::LONG_NAME, 'CRM_PaypalImporter_Request_AuthMock', 'fake_transactions_class_name');
        $result = $p->run(self::PARAMS);
        self::assertTrue(array_key_exists('is_error', $result));
        self::assertSame(0, $result['is_error']);
        self::assertTrue(array_key_exists('values', $result));
        self::assertTrue(array_key_exists('stats', $result['values']));
        self::assertTrue(array_key_exists('execution-time', $result['values']['stats']));
        self::assertIsFloat($result['values']['stats']['execution-time']);
        self::assertTrue(array_key_exists('new-user', $result['values']['stats']));
        self::assertSame(0, $result['values']['stats']['new-user']);
        self::assertTrue(array_key_exists('transaction', $result['values']['stats']));
        self::assertSame(0, $result['values']['stats']['transaction']);
        self::assertTrue(array_key_exists('errors', $result['values']['stats']));
        self::assertSame([], $result['values']['stats']['errors']);
    }
    public function testRunTransactionSearchFail()
    {
        $this->setupTestConfig();
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        $config->updateState('import-init');
        $p = new CRM_PaypalImporter_ImportProcess(E::LONG_NAME, 'CRM_PaypalImporter_Request_AuthMock', 'CRM_PaypalImporter_Request_TransactionsCodeMock');
        self::expectException(API_Exception::class);
        $p->run(self::PARAMS);
    }
    public function testRunTransactionSearchNoTransactionsAfterInit()
    {
        $this->setupTestConfig();
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        $config->updateState('import-init');
        $settings = [
            'client-id' => 'clientid',
            'client-secret' => 'clientsecret',
            'paypal-host' => 'https://host.com',
            'start-date' => date('Y-m-d H:i', strtotime('now - 61 days')),
            'import-limit' => 1,
            'financial-type-id' => '1',
            'payment-instrument-id' => '1',
            'request-limit' => 1,
        ];
        $config->updateSettings($settings);
        $p = new CRM_PaypalImporter_ImportProcess(E::LONG_NAME, 'CRM_PaypalImporter_Request_AuthMock', 'CRM_PaypalImporter_Request_TransactionsNoTransactionMock');
        $result = $p->run(self::PARAMS);
        self::assertTrue(array_key_exists('is_error', $result));
        self::assertSame(0, $result['is_error']);
        self::assertTrue(array_key_exists('values', $result));
        self::assertTrue(array_key_exists('stats', $result['values']));
        self::assertTrue(array_key_exists('execution-time', $result['values']['stats']));
        self::assertIsFloat($result['values']['stats']['execution-time']);
        self::assertTrue(array_key_exists('new-user', $result['values']['stats']));
        self::assertSame(0, $result['values']['stats']['new-user']);
        self::assertTrue(array_key_exists('transaction', $result['values']['stats']));
        self::assertSame(0, $result['values']['stats']['transaction']);
        self::assertTrue(array_key_exists('errors', $result['values']['stats']));
        self::assertSame([], $result['values']['stats']['errors']);
        $config->load();
        $cfg = $config->get();
        // the page has to be 1 again, and the start date has to be increased with 30 days.
        self::assertSame(1, $cfg['import-params']['page'], 'Invalid page after the first iteration');
        self::assertSame(date('Y-m-d H:i', strtotime($settings['start-date'] .' + 30 days')), $cfg['import-params']['start-date'], 'Invalid start-date after the first iteration');
        self::assertSame('import', $cfg['state']);
        // next iteration, page 1, date increased with 30 days again.
        $p = new CRM_PaypalImporter_ImportProcess(E::LONG_NAME, 'CRM_PaypalImporter_Request_AuthMock', 'CRM_PaypalImporter_Request_TransactionsNoTransactionMock');
        $result = $p->run(self::PARAMS);
        $config->load();
        $cfg = $config->get();
        self::assertSame(1, $cfg['import-params']['page'], 'Invalid page after the second iteration');
        self::assertSame(date('Y-m-d H:i', strtotime($settings['start-date'] .' + 60 days')), $cfg['import-params']['start-date'], 'Invalid start-date after the second iteration');
        self::assertSame('import', $cfg['state']);
        // last iteration, sync state.
        $p = new CRM_PaypalImporter_ImportProcess(E::LONG_NAME, 'CRM_PaypalImporter_Request_AuthMock', 'CRM_PaypalImporter_Request_TransactionsNoTransactionMock');
        $result = $p->run(self::PARAMS);
        $config->load();
        $cfg = $config->get();
        self::assertSame(1, $cfg['import-params']['page'], 'Invalid page after the last iteration');
        self::assertSame('sync', $cfg['state'], 'Invalid final state.');
    }
    public function testRunTransactionWithoutEmailData()
    {
        $this->setupTestConfig();
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        $config->updateState('import-init');
        $settings = [
            'client-id' => 'clientid',
            'client-secret' => 'clientsecret',
            'paypal-host' => 'https://host.com',
            'start-date' => date('Y-m-d H:i', strtotime('now - 61 days')),
            'import-limit' => 1,
            'financial-type-id' => '1',
            'payment-instrument-id' => '1',
            'request-limit' => 1,
        ];
        $config->updateSettings($settings);
        $p = new CRM_PaypalImporter_ImportProcess(E::LONG_NAME, 'CRM_PaypalImporter_Request_AuthMock', 'CRM_PaypalImporter_Request_TransactionsMissingEmailMock');
        $result = $p->run(self::PARAMS);
        self::assertTrue(array_key_exists('is_error', $result));
        self::assertSame(0, $result['is_error']);
        self::assertTrue(array_key_exists('values', $result));
        self::assertTrue(array_key_exists('stats', $result['values']));
        self::assertTrue(array_key_exists('execution-time', $result['values']['stats']));
        self::assertIsFloat($result['values']['stats']['execution-time']);
        self::assertTrue(array_key_exists('new-user', $result['values']['stats']));
        self::assertSame(0, $result['values']['stats']['new-user']);
        self::assertTrue(array_key_exists('transaction', $result['values']['stats']));
        self::assertSame(0, $result['values']['stats']['transaction']);
        self::assertTrue(array_key_exists('errors', $result['values']['stats']));
        self::assertSame([CRM_PaypalImporter_Request_TransactionsMissingEmailMock::TRANSACTION_ID.' | Skipping transaction due to missing email address.'], $result['values']['stats']['errors']);
        $config->load();
        $cfg = $config->get();
        // the page has to be 1 again, and the start date has to be increased with 30 days.
        self::assertSame(1, $cfg['import-params']['page'], 'Invalid page after the first iteration');
        self::assertSame(date('Y-m-d H:i', strtotime($settings['start-date'] .' + 30 days')), $cfg['import-params']['start-date'], 'Invalid start-date after the first iteration');
        self::assertSame('import', $cfg['state']);
    }
    public function testRunTransactionWithTransactions()
    {
        /*
         * According to my tests, the monetary settings are not set well.
         * '5TY05013RG002845M | Failed to create Contribution, reason: Function money_format() is deprecated',
         * I'm thinking about something like i have in the local dev installer script.
         */
        $results = civicrm_api4('Setting', 'set', [
            'values' => [
                'monetaryThousandSeparator' => '.',
                'monetaryDecimalPoint' => ',',
                'moneyformat' => '%a %c',
            ],
        ]);
        $this->setupTestConfig();
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        $config->updateState('import-init');
        $settings = [
            'client-id' => 'clientid',
            'client-secret' => 'clientsecret',
            'paypal-host' => 'https://host.com',
            'start-date' => date('Y-m-d H:i', strtotime('now - 61 days')),
            'import-limit' => 2,
            'financial-type-id' => '1',
            'payment-instrument-id' => '1',
            'request-limit' => 1,
        ];
        $config->updateSettings($settings);
        $p = new CRM_PaypalImporter_ImportProcess(E::LONG_NAME, 'CRM_PaypalImporter_Request_AuthMock', 'CRM_PaypalImporter_Request_TransactionsMock');
        $result = $p->run(self::PARAMS);
        echo var_export($result, true);
        self::assertTrue(array_key_exists('is_error', $result));
        self::assertSame(0, $result['is_error']);
        self::assertTrue(array_key_exists('values', $result));
        self::assertTrue(array_key_exists('stats', $result['values']));
        self::assertTrue(array_key_exists('execution-time', $result['values']['stats']));
        self::assertIsFloat($result['values']['stats']['execution-time']);
        self::assertTrue(array_key_exists('new-user', $result['values']['stats']));
        self::assertSame(2, $result['values']['stats']['new-user']);
        self::assertTrue(array_key_exists('transaction', $result['values']['stats']));
        self::assertSame(2, $result['values']['stats']['transaction']);
        self::assertTrue(array_key_exists('errors', $result['values']['stats']));
        self::assertSame([], $result['values']['stats']['errors']);
        $config->load();
        $cfg = $config->get();
        // the page has to be 1 again, and the start date has to be increased with 30 days.
        self::assertSame(1, $cfg['import-params']['page'], 'Invalid page after the first iteration');
        self::assertSame(date('Y-m-d H:i', strtotime($settings['start-date'] .' + 30 days')), $cfg['import-params']['start-date'], 'Invalid start-date after the first iteration');
        self::assertSame('import', $cfg['state']);
    }
}

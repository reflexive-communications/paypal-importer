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
}

<?php

use CRM_PaypalImporter_ExtensionUtil as E;

/**
 * PaypalDataImport.Process API Test Case
 * This is a generic test class implemented with PHPUnit.
 * @group headless
 */
class api_v3_PaypalDataImport_ProcessTest extends CRM_PaypalImporter_HeadlessBase
{
    use \Civi\Test\Api3TestTrait;

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
            'tag-id' => 0,
            'group-id' => 0,
        ],
        'state' => 'do-nothing',
        'import-params' => [
        ],
        'import-stats' => [
        ],
        'import-error' => '',
    ];

    /**
     * Simple example test case.
     * Just call the endpoint. Due to the default state, it will do nothing.
     */
    public function testApiCall()
    {
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        self::assertTrue($config->update(self::TEST_SETTINGS), 'Config update has to be successful.');
        $result = civicrm_api3('PaypalDataImport', 'process', []);
        $this->assertEquals(self::TEST_SETTINGS['state'], $result['values']['state']);
    }
}

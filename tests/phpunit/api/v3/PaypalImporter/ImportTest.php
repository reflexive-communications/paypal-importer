<?php

use Civi\PaypalImporter\Config;
use Civi\PaypalImporter\HeadlessTestCase;
use CRM_PaypalImporter_ExtensionUtil as E;

/**
 * @group headless
 */
class api_v3_PaypalImporter_ImportTest extends HeadlessTestCase
{
    public const TEST_SETTINGS = [
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
     * @return void
     * @throws \CiviCRM_API3_Exception
     */
    public function testApiCall()
    {
        $config = new Config(E::LONG_NAME);
        self::assertTrue($config->update(self::TEST_SETTINGS), 'Config update has to be successful.');
        $result = civicrm_api3('PaypalImporter', 'import');
        $this->assertEquals(self::TEST_SETTINGS['state'], $result['values']['state']);
    }
}

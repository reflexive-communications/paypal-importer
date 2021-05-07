<?php

use CRM_PaypalImporter_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * PaypalDataImport.Process API Test Case
 * This is a generic test class implemented with PHPUnit.
 * @group headless
 */
class api_v3_PaypalDataImport_ProcessTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface
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
     * Set up for headless tests.
     *
     * Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
     *
     * See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
     */
    public function setUpHeadless()
    {
        return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
    }

    /**
     * The setup() method is executed before the test is executed (optional).
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * The tearDown() method is executed after the test was executed (optional)
     * This can be used for cleanup.
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }
    /**
     * Simple example test case.
     *
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

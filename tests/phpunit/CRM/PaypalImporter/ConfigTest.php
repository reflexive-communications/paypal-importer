<?php

use Civi\PaypalImporter\HeadlessTestCase;

/**
 * Testcases for the configuration.
 *
 * @group headless
 */
class CRM_PaypalImporter_ConfigTest extends HeadlessTestCase
{
    /**
     * It checks that the create function works well.
     */
    public function testCreate()
    {
        $config = new CRM_PaypalImporter_Config('paypal_test');
        self::assertTrue($config->create(), 'Create config has to be successful.');
        $cfg = $config->get();
        self::assertTrue(array_key_exists('settings', $cfg), 'settings key is missing from the config.');
        self::assertTrue(array_key_exists('client-id', $cfg['settings']), 'client-id key is missing from the config.');
        self::assertSame('', $cfg['settings']['client-id'], 'Invalid client-id initial value.');
        self::assertTrue(array_key_exists('import-limit', $cfg['settings']), 'import-limit key is missing from the config.');
        self::assertSame(1, $cfg['settings']['import-limit'], 'Invalid import-limit initial value.');
        self::assertTrue(array_key_exists('client-secret', $cfg['settings']), 'client-secret key is missing from the config.');
        self::assertSame('', $cfg['settings']['client-secret'], 'Invalid client-secret initial value.');
        self::assertTrue(array_key_exists('paypal-host', $cfg['settings']), 'paypal-host key is missing from the config.');
        self::assertSame('', $cfg['settings']['paypal-host'], 'Invalid paypal-host initial value.');
        self::assertTrue(array_key_exists('start-date', $cfg['settings']), 'start-date key is missing from the config.');
        self::assertSame('', $cfg['settings']['start-date'], 'Invalid start-date initial value.');
        self::assertTrue(array_key_exists('financial-type-id', $cfg['settings']), 'financial-type-id key is missing from the config.');
        self::assertSame('', $cfg['settings']['financial-type-id'], 'Invalid financial-type-id initial value.');
        self::assertTrue(array_key_exists('payment-instrument-id', $cfg['settings']), 'payment-instrument-id key is missing from the config.');
        self::assertSame('', $cfg['settings']['payment-instrument-id'], 'Invalid payment-instrument-id initial value.');
        self::assertTrue(array_key_exists('request-limit', $cfg['settings']), 'request-limit key is missing from the config.');
        self::assertSame(1, $cfg['settings']['request-limit'], 'Invalid request-limit initial value.');
        self::assertTrue(array_key_exists('tag-id', $cfg['settings']), 'tag-id key is missing from the config.');
        self::assertSame(0, $cfg['settings']['tag-id'], 'Invalid tag-id initial value.');
        self::assertTrue(array_key_exists('group-id', $cfg['settings']), 'group-id key is missing from the config.');
        self::assertSame(0, $cfg['settings']['group-id'], 'Invalid group-id initial value.');
        self::assertTrue(array_key_exists('state', $cfg), 'state key is missing from the config.');
        self::assertSame('do-nothing', $cfg['state'], 'Invalid state initial value.');
        self::assertTrue(array_key_exists('import-params', $cfg), 'import-params key is missing from the config.');
        self::assertSame([], $cfg['import-params'], 'Invalid import-params initial value.');
        self::assertTrue(array_key_exists('import-stats', $cfg), 'import-stats key is missing from the config.');
        self::assertSame([], $cfg['import-stats'], 'Invalid import-stats initial value.');
        self::assertTrue(array_key_exists('import-error', $cfg), 'import-error key is missing from the config.');
        self::assertSame('', $cfg['import-error'], 'Invalid import-error initial value.');

        self::assertTrue($config->create(), 'Create config has to be successful multiple times.');
    }

    /**
     * It checks that the remove function works well.
     */
    public function testRemove()
    {
        $config = new CRM_PaypalImporter_Config('paypal_test');
        self::assertTrue($config->create(), 'Create config has to be successful.');
        self::assertTrue($config->remove(), 'Remove config has to be successful.');
    }

    /**
     * It checks that the get function works well.
     */
    public function testGet()
    {
        $config = new CRM_PaypalImporter_Config('paypal_test');
        self::assertTrue($config->create(), 'Create config has to be successful.');
        $cfg = $config->get();
        self::assertTrue(array_key_exists('settings', $cfg), 'settings key is missing from the config.');
        self::assertTrue(array_key_exists('client-id', $cfg['settings']), 'client-id key is missing from the config.');
        self::assertSame('', $cfg['settings']['client-id'], 'Invalid client-id initial value.');
        self::assertTrue(array_key_exists('import-limit', $cfg['settings']), 'import-limit key is missing from the config.');
        self::assertSame(1, $cfg['settings']['import-limit'], 'Invalid import-limit initial value.');
        self::assertTrue(array_key_exists('client-secret', $cfg['settings']), 'client-secret key is missing from the config.');
        self::assertSame('', $cfg['settings']['client-secret'], 'Invalid client-secret initial value.');
        self::assertTrue(array_key_exists('paypal-host', $cfg['settings']), 'paypal-host key is missing from the config.');
        self::assertSame('', $cfg['settings']['paypal-host'], 'Invalid paypal-host initial value.');
        self::assertTrue(array_key_exists('start-date', $cfg['settings']), 'start-date key is missing from the config.');
        self::assertSame('', $cfg['settings']['start-date'], 'Invalid start-date initial value.');
        self::assertTrue(array_key_exists('financial-type-id', $cfg['settings']), 'financial-type-id key is missing from the config.');
        self::assertSame('', $cfg['settings']['financial-type-id'], 'Invalid financial-type-id initial value.');
        self::assertTrue(array_key_exists('payment-instrument-id', $cfg['settings']), 'payment-instrument-id key is missing from the config.');
        self::assertSame('', $cfg['settings']['payment-instrument-id'], 'Invalid payment-instrument-id initial value.');
        self::assertTrue(array_key_exists('request-limit', $cfg['settings']), 'request-limit key is missing from the config.');
        self::assertSame(1, $cfg['settings']['request-limit'], 'Invalid request-limit initial value.');
        self::assertTrue(array_key_exists('tag-id', $cfg['settings']), 'tag-id key is missing from the config.');
        self::assertSame(0, $cfg['settings']['tag-id'], 'Invalid tag-id initial value.');
        self::assertTrue(array_key_exists('group-id', $cfg['settings']), 'group-id key is missing from the config.');
        self::assertSame(0, $cfg['settings']['group-id'], 'Invalid group-id initial value.');
        self::assertTrue(array_key_exists('state', $cfg), 'state key is missing from the config.');
        self::assertSame('do-nothing', $cfg['state'], 'Invalid state initial value.');
        self::assertTrue(array_key_exists('import-params', $cfg), 'import-params key is missing from the config.');
        self::assertSame([], $cfg['import-params'], 'Invalid import-params initial value.');
        self::assertTrue(array_key_exists('import-stats', $cfg), 'import-stats key is missing from the config.');
        self::assertSame([], $cfg['import-stats'], 'Invalid import-stats initial value.');
        self::assertTrue(array_key_exists('import-error', $cfg), 'import-error key is missing from the config.');
        self::assertSame('', $cfg['import-error'], 'Invalid import-error initial value.');

        self::assertTrue($config->remove(), 'Remove config has to be successful.');
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('paypal_test_config config is missing.');
        $cfg = $config->get();
    }

    /**
     * It checks that the update function works well.
     */
    public function testUpdate()
    {
        $config = new CRM_PaypalImporter_Config('paypal_test');
        self::assertTrue($config->create(), 'Create config has to be successful.');
        $cfg = $config->get();
        $cfg['settings']['client-id'] = 'new-client-id';
        self::assertTrue($config->update($cfg), 'Update config has to be successful.');
        $cfgUpdated = $config->get();
        self::assertEquals($cfg, $cfgUpdated, 'Invalid updated configuration.');
    }

    /**
     * It checks that the load function works well.
     */
    public function testLoad()
    {
        $config = new CRM_PaypalImporter_Config('paypal_test');
        self::assertTrue($config->create(), 'Create config has to be successful.');
        $cfg = $config->get();
        $cfg['settings']['client-id'] = 'new-client-id';
        self::assertTrue($config->update($cfg), 'Update config has to be successful.');
        $cfgUpdated = $config->get();
        self::assertEquals($cfg, $cfgUpdated, 'Invalid updated configuration.');
        $otherConfig = new CRM_PaypalImporter_Config('paypal_test');
        self::assertEmpty($otherConfig->load(), 'Load result supposed to be empty.');

        $cfgLoaded = $otherConfig->get();
        self::assertEquals($cfg, $cfgLoaded, 'Invalid loaded configuration.');

        $missingConfig = new CRM_PaypalImporter_Config('paypal_test_missing_config');
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('paypal_test_missing_config_config config invalid.');
        self::assertEmpty($missingConfig->load(), 'Load result supposed to be empty.');
    }

    /**
     * It checks that the updateSettings function works well.
     */
    public function testUpdateSettings()
    {
        $config = new CRM_PaypalImporter_Config('paypal_test');
        self::assertTrue($config->create(), 'Create config has to be successful.');
        $cfg = $config->get();
        $cfg['settings']['client-id'] = 'new-client-id';
        self::assertTrue($config->updateSettings($cfg['settings']), 'Update config has to be successful.');
        $cfgUpdated = $config->get();
        self::assertEquals($cfg, $cfgUpdated, 'Invalid updated configuration.');
    }

    /**
     * It checks that the updateSettings function works well.
     */
    public function updateState()
    {
        $config = new CRM_PaypalImporter_Config('paypal_test');
        self::assertTrue($config->create(), 'Create config has to be successful.');
        $cfg = $config->get();
        $cfg['state'] = 'error';
        self::assertTrue($config->updateState($cfg['state']), 'Update config has to be successful.');
        $cfgUpdated = $config->get();
        self::assertEquals($cfg, $cfgUpdated, 'Invalid updated configuration.');
    }

    /**
     * It checks that the updateImportParams function works well.
     */
    public function testUpdateImportParams()
    {
        $config = new CRM_PaypalImporter_Config('paypal_test');
        self::assertTrue($config->create(), 'Create config has to be successful.');
        $cfg = $config->get();
        $cfg['import-params'] = [
            'page' => 1,
            'start-date' => $cfg['settings']['start-date'],
        ];
        self::assertTrue($config->updateImportParams($cfg['import-params']), 'Update config has to be successful.');
        $cfgUpdated = $config->get();
        self::assertEquals($cfg, $cfgUpdated, 'Invalid updated configuration.');
    }

    /**
     * It checks that the updateImportStats function works well.
     */
    public function testUpdateImportStats()
    {
        $config = new CRM_PaypalImporter_Config('paypal_test');
        self::assertTrue($config->create(), 'Create config has to be successful.');
        $cfg = $config->get();
        $cfg['import-stats'] = [
            'new-user' => 1,
            'transaction' => 2,
            'errors' => [],
        ];
        self::assertTrue($config->updateImportStats($cfg['import-stats']), 'Update config has to be successful.');
        $cfgUpdated = $config->get();
        self::assertEquals($cfg, $cfgUpdated, 'Invalid updated configuration.');
    }

    /**
     * It checks that the updateImportError function works well.
     */
    public function testUpdateImportError()
    {
        $config = new CRM_PaypalImporter_Config('paypal_test');
        self::assertTrue($config->create(), 'Create config has to be successful.');
        $cfg = $config->get();
        $cfg['import-error'] = 'new-issue something went wrong.';
        self::assertTrue($config->updateImportError($cfg['import-error']), 'Update config has to be successful.');
        $cfgUpdated = $config->get();
        self::assertEquals($cfg, $cfgUpdated, 'Invalid updated configuration.');
    }

    /**
     * It checks that the updateState function works well.
     */
    public function testUpdateState()
    {
        $config = new CRM_PaypalImporter_Config('paypal_test');
        self::assertTrue($config->create(), 'Create config has to be successful.');
        $cfg = $config->get();
        $cfg['state'] = 'import';
        self::assertTrue($config->updateState($cfg['state']), 'Update config has to be successful.');
        $cfgUpdated = $config->get();
        self::assertEquals($cfg, $cfgUpdated, 'Invalid updated configuration.');
    }
}

<?php

/**
 * Testcases for the configuration.
 *
 * @group headless
 */
class CRM_PaypalImporter_ConfigHeadlessTest extends CRM_PaypalImporter_HeadlessBase
{
    /**
     * It checks that the create function works well.
     */
    public function testCreate()
    {
        $config = new CRM_PaypalImporter_Config("paypal_test");
        self::assertTrue($config->create(), "Create config has to be successful.");
        $cfg = $config->get();
        self::assertTrue(array_key_exists("client-id", $cfg), "client-id key is missing from the config.");
        self::assertSame("", $cfg["client-id"], "Invalid client-id initial value.");
        self::assertTrue(array_key_exists("import-limit", $cfg), "import-limit key is missing from the config.");
        self::assertSame(1, $cfg["import-limit"], "Invalid import-limit initial value.");
        self::assertTrue(array_key_exists("client-secret", $cfg), "client-secret key is missing from the config.");
        self::assertSame("", $cfg["client-secret"], "Invalid client-secret initial value.");
        self::assertTrue(array_key_exists("paypal-host", $cfg), "paypal-host key is missing from the config.");
        self::assertSame("", $cfg["paypal-host"], "Invalid paypal-host initial value.");
        self::assertTrue(array_key_exists("start-date", $cfg), "start-date key is missing from the config.");
        self::assertSame("", $cfg["start-date"], "Invalid start-date initial value.");

        self::assertTrue($config->create(), "Create config has to be successful multiple times.");
    }

    /**
     * It checks that the remove function works well.
     */
    public function testRemove()
    {
        $config = new CRM_PaypalImporter_Config("paypal_test");
        self::assertTrue($config->create(), "Create config has to be successful.");
        self::assertTrue($config->remove(), "Remove config has to be successful.");
    }

    /**
     * It checks that the get function works well.
     */
    public function testGet()
    {
        $config = new CRM_PaypalImporter_Config("paypal_test");
        self::assertTrue($config->create(), "Create config has to be successful.");
        $cfg = $config->get();
        self::assertTrue(array_key_exists("client-id", $cfg), "client-id key is missing from the config.");
        self::assertSame("", $cfg["client-id"], "Invalid client-id initial value.");
        self::assertTrue(array_key_exists("import-limit", $cfg), "import-limit key is missing from the config.");
        self::assertSame(1, $cfg["import-limit"], "Invalid import-limit initial value.");
        self::assertTrue(array_key_exists("client-secret", $cfg), "client-secret key is missing from the config.");
        self::assertSame("", $cfg["client-secret"], "Invalid client-secret initial value.");
        self::assertTrue(array_key_exists("paypal-host", $cfg), "paypal-host key is missing from the config.");
        self::assertSame("", $cfg["paypal-host"], "Invalid paypal-host initial value.");
        self::assertTrue(array_key_exists("start-date", $cfg), "start-date key is missing from the config.");
        self::assertSame("", $cfg["start-date"], "Invalid start-date initial value.");

        self::assertTrue($config->remove(), "Remove config has to be successful.");
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage("paypal_test_config config is missing.");
        $cfg = $config->get();
    }

    /**
     * It checks that the get function works well.
     */
    public function testUpdate()
    {
        $config = new CRM_PaypalImporter_Config("paypal_test");
        self::assertTrue($config->create(), "Create config has to be successful.");
        $cfg = $config->get();
        $cfg["client-id"] = "new-client-id";
        self::assertTrue($config->update($cfg), "Update config has to be successful.");
        $cfgUpdated = $config->get();
        self::assertEquals($cfg, $cfgUpdated, "Invalid updated configuration.");
    }

    /**
     * It checks that the get function works well.
     */
    public function testLoad()
    {
        $config = new CRM_PaypalImporter_Config("paypal_test");
        self::assertTrue($config->create(), "Create config has to be successful.");
        $cfg = $config->get();
        $cfg["client-id"] = "new-client-id";
        self::assertTrue($config->update($cfg), "Update config has to be successful.");
        $cfgUpdated = $config->get();
        self::assertEquals($cfg, $cfgUpdated, "Invalid updated configuration.");
        $otherConfig = new CRM_PaypalImporter_Config("paypal_test");
        self::assertEmpty($otherConfig->load(), "Load result supposed to be empty.");

        $cfgLoaded = $otherConfig->get();
        self::assertEquals($cfg, $cfgLoaded, "Invalid loaded configuration.");

        $missingConfig = new CRM_PaypalImporter_Config("paypal_test_missing_config");
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage("paypal_test_missing_config_config config invalid.");
        self::assertEmpty($missingConfig->load(), "Load result supposed to be empty.");
    }
}

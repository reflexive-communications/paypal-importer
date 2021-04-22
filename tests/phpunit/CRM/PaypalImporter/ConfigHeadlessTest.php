<?php

use CRM_PaypalImporter_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * FIXME - Add test description.
 *
 * @group headless
 */
class CRM_PaypalImporter_ConfigHeadlessTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

      /**
     * Apply a forced rebuild of DB, thus
     * create a clean DB before running tests
     *
     * @throws \CRM_Extension_Exception_ParseException
     */
    public static function setUpBeforeClass(): void
    {
        // Resets DB and install depended extension
        \Civi\Test::headless()
            ->install('rc-base')
            ->installMe(__DIR__)
            ->apply(true);
    }

      /**
     * Create a clean DB before running tests
     *
     * @throws CRM_Extension_Exception_ParseException
     */
    public static function tearDownAfterClass(): void
    {
        \Civi\Test::headless()
            ->uninstallMe(__DIR__)
            ->uninstall('rc-base')
            ->apply(true);
    }

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }
    /**
     * It checks that the create function works well.
     */
    public function testCreate()
    {
        $config = new CRM_PaypalImporter_Config("paypal_test");
        self::assertTrue($config->create(), "Create config has to be successful.");
        $cfg = $config->get();
        self::assertTrue(array_key_exists("api-key", $cfg), "api-key key is missing from the config.");
        self::assertSame("", $cfg["api-key"], "Invalid api-key initial value.");
        self::assertTrue(array_key_exists("import-limit", $cfg), "import-limit key is missing from the config.");
        self::assertSame(1, $cfg["import-limit"], "Invalid import-limit initial value.");

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
        self::assertTrue(array_key_exists("api-key", $cfg), "api-key key is missing from the config.");
        self::assertSame("", $cfg["api-key"], "Invalid api-key initial value.");
        self::assertTrue(array_key_exists("import-limit", $cfg), "import-limit key is missing from the config.");
        self::assertSame(1, $cfg["import-limit"], "Invalid import-limit initial value.");

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
        $cfg["api-key"] = "new-api-key";
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
        $cfg["api-key"] = "new-api-key";
        self::assertTrue($config->update($cfg), "Update config has to be successful.");
        $cfgUpdated = $config->get();
        self::assertEquals($cfg, $cfgUpdated, "Invalid updated configuration.");
        $otherConfig = new CRM_PaypalImporter_Config("civalpa_test");
        self::assertEmpty($otherConfig->load(), "Load result supposed to be empty.");

        $cfgLoaded = $otherConfig->get();
        self::assertEquals($cfg, $cfgLoaded, "Invalid loaded configuration.");

        $missingConfig = new CRM_PaypalImporter_Config("paypal_test_missing_config");
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage("paypal_test_missing_config_config config invalid.");
        self::assertEmpty($missingConfig->load(), "Load result supposed to be empty.");
    }
}

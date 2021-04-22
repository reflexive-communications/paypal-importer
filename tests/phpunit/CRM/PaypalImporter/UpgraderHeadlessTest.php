<?php

/**
 * Installer application tests.
 *
 * @group headless
 */
class CRM_PaypalImporter_UpgraderHeadlessTest extends CRM_PaypalImporter_HeadlessBase
{
    /**
     * Test the install process.
     */
    public function testInstall()
    {
        $installer = new CRM_PaypalImporter_Upgrader("paypal_test", ".");
        try {
            $this->assertEmpty($installer->install());
        } catch (Exception $e) {
            $this->fail("Should not throw exception.");
        }
    }

    /**
     * Test the uninstall process.
     */
    public function testUninstall()
    {
        $installer = new CRM_PaypalImporter_Upgrader("paypal_test", ".");
        $this->assertEmpty($installer->install());
        try {
            $this->assertEmpty($installer->uninstall());
        } catch (Exception $e) {
            $this->fail("Should not throw exception.");
        }
    }
}

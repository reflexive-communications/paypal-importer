<?php

use Civi\PaypalImporter\HeadlessTestCase;

/**
 * @group headless
 */
class CRM_PaypalImporter_UpgraderTest extends HeadlessTestCase
{
    /**
     * @return void
     */
    public function testInstall()
    {
        $installer = new CRM_PaypalImporter_Upgrader();
        try {
            $this->assertEmpty($installer->install());
        } catch (Exception $e) {
            $this->fail('Should not throw exception.');
        }
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testUninstall()
    {
        $installer = new CRM_PaypalImporter_Upgrader();
        $this->assertEmpty($installer->install());
        try {
            $this->assertEmpty($installer->uninstall());
        } catch (Exception $e) {
            $this->fail('Should not throw exception.');
        }
    }
}

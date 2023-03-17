<?php

use CRM_PaypalImporter_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_PaypalImporter_Upgrader extends CRM_Extension_Upgrader_Base
{
    /**
     * Write error to Civi log
     *
     * @param string $message Error message
     *
     * @return void
     */
    public static function logError(string $message): void
    {
        Civi::log()->error(sprintf('PayPal Importer: %s', $message));
    }

    /**
     * Install process. Init database.
     *
     * @throws CRM_Core_Exception
     */
    public function install(): void
    {
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        // Create default configs
        if (!$config->create()) {
            CRM_PaypalImporter_Upgrader::logError(E::LONG_NAME.ts(' could not create configs in database'));
            throw new CRM_Core_Exception(E::LONG_NAME.ts(' could not create configs in database'));
        }
    }

    /**
     * Uninstall process. Clean database.
     *
     * @throws CRM_Core_Exception
     */
    public function uninstall(): void
    {
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        // delete current configs
        if (!$config->remove()) {
            CRM_PaypalImporter_Upgrader::logError(E::LONG_NAME.ts(' could not remove configs from database'));
            throw new CRM_Core_Exception(E::LONG_NAME.ts(' could not remove configs from database'));
        }
    }
}

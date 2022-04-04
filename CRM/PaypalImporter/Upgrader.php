<?php

/**
 * Collection of upgrade steps.
 */
class CRM_PaypalImporter_Upgrader extends CRM_PaypalImporter_Upgrader_Base
{
    /**
     * Write error to Civi log
     *
     * @param string $message Error message
     *
     * @return void
     */
    public static function logError(string $message)
    {
        Civi::log()->error(sprintf('PayPal Importer: %s', $message));
    }

    /**
     * Install process. Init database.
     *
     * @throws CRM_Core_Exception
     */
    public function install()
    {
        $config = new CRM_PaypalImporter_Config($this->extensionName);
        // Create default configs
        if (!$config->create()) {
            CRM_PaypalImporter_Upgrader::logError($this->extensionName.ts(' could not create configs in database'));
            throw new CRM_Core_Exception($this->extensionName.ts(' could not create configs in database'));
        }
    }

    /**
     * Uninstall process. Clean database.
     *
     * @throws CRM_Core_Exception
     */
    public function uninstall()
    {
        $config = new CRM_PaypalImporter_Config($this->extensionName);
        // delete current configs
        if (!$config->remove()) {
            CRM_PaypalImporter_Upgrader::logError($this->extensionName.ts(' could not remove configs from database'));
            throw new CRM_Core_Exception($this->extensionName.ts(' could not remove configs from database'));
        }
    }
}

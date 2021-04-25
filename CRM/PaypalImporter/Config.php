<?php

class CRM_PaypalImporter_Config extends CRM_RcBase_Config
{
    /**
     * Provides a default configuration object.
     *
     * @return array the default configuration object.
     */
    public function defaultConfiguration(): array
    {
        return [
            'settings' => [
                'client-id' => '',
                'client-secret' => '',
                'paypal-host' => '',
                'start-date' => '',
                'import-limit' => 1,
                'financial-type-id' => '',
                'payment-instrument-id' => '',
            ],
            'state' => 'do-nothing',
        ];
    }

    /**
     * Updates the settings.
     *
     * @param array $settings the data to save
     *
     * @return bool the status of the update process.
     *
     * @throws CRM_Core_Exception.
     */
    public function updateSettings(array $settings): bool
    {
        // load latest config
        parent::load();
        $configuration = parent::get();
        $configuration['settings'] = $settings;
        return parent::update($configuration);
    }
}

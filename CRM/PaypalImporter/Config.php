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
                'request-limit' => 1,
            ],
            'state' => 'do-nothing',
            'import-params' => [
            ],
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

    /**
     * Updates the State.
     *
     * @param string $state the state name to save
     *
     * @return bool the status of the update process.
     *
     * @throws CRM_Core_Exception.
     */
    public function updateState(string $state): bool
    {
        // load latest config
        parent::load();
        $configuration = parent::get();
        $configuration['state'] = $state;
        return parent::update($configuration);
    }

    /**
     * Updates the import-params.
     *
     * @param array $params the data to save
     *
     * @return bool the status of the update process.
     *
     * @throws CRM_Core_Exception.
     */
    public function updateImportParams(array $params): bool
    {
        // load latest config
        parent::load();
        $configuration = parent::get();
        $configuration['import-params'] = $params;
        return parent::update($configuration);
    }
}

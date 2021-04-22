<?php

use CRM_PaypalImporter_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_PaypalImporter_Form_Settings extends CRM_Core_Form
{
    /**
     * Configdb
     *
     * @var CRM_PaypalImporter_Config
     */
    private $config;

    /**
     * Preprocess form
     *
     * @throws CRM_Core_Exception
     */
    public function preProcess()
    {
        // Get current settings
        $this->config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        $this->config->load();
    }

    /**
     * Set default values
     *
     * @return array
     */
    public function setDefaultValues()
    {
        $config = $this->config->get();
        // Set defaults
        $this->_defaults['apiKey'] = $config['api-key'];
        $this->_defaults['importLimit'] = $config['import-limit'];

        return $this->_defaults;
    }

    /**
     * Register validation rules
     * The import limit has to be numeric value. Client + server side validation.
     */
    public function addRules() {
        $this->addRule('importLimit', ts('The import limit has to be numeric.'), 'numeric', null, 'client');
        $this->addRule('importLimit', ts('The import limit has to be numeric.'), 'numeric');
    }

    /**
     * Build form
     */
    public function buildQuickForm()
    {
        // get the current configuration object
        $config = $this->config->get();
        // Add form elements
        $this->add('text', 'apiKey', ts('API key'), [], true);
        $this->add('text', 'importLimit', ts('Import limit'), [], true);
        // Submit button
        $this->addButtons(
            [
                [
                    'type' => 'submit',
                    'name' => ts('Save'),
                    'isDefault' => true,
                ],
            ]
        );
        $this->setTitle(ts('Paypal data importer'));
    }

    /**
     * Process post data
     */
    public function postProcess()
    {
        $submitData = [
            'api-key' => $this->_submitValues['apiKey'],
            'import-limit' => intval($this->_submitValues['importLimit'], 10),
        ];
        try {
            if (!$this->config->update($submitData)) {
                CRM_Core_Session::setStatus(ts('Error during save process'), 'Paypal Importer', 'error');
            } else {
                CRM_Core_Session::setStatus(ts('Data has been updated.'), 'Paypal Importer', 'success', ['expires' => 5000,]);
            }
        } catch (CRM_Core_Exception $e) {
            CRM_Core_Session::setStatus(ts($e->getMessage()), 'Paypal Importer', 'error');
        }
        parent::postProcess();
    }
}

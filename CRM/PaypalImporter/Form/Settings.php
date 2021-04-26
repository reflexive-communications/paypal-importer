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
        $this->_defaults['clientId'] = $config['settings']['client-id'];
        $this->_defaults['clientSecret'] = $config['settings']['client-secret'];
        $this->_defaults['paypalHost'] = $config['settings']['paypal-host'];
        $this->_defaults['startDate'] = $config['settings']['start-date'];
        $this->_defaults['importLimit'] = $config['settings']['import-limit'];
        $this->_defaults['requestLimit'] = $config['settings']['request-limit'];
        $this->_defaults['paymentInstrumentId'] = $config['settings']['payment-instrument-id'];
        $this->_defaults['financialTypeId'] = $config['settings']['financial-type-id'];

        return $this->_defaults;
    }

    /**
     * Register validation rules
     * The import limit has to be numeric value. Client + server side validation.
     */
    public function addRules()
    {
        $this->addRule('importLimit', ts('The import limit has to be numeric.'), 'numeric', null, 'client');
        $this->addRule('importLimit', ts('The import limit has to be numeric.'), 'numeric');
        $this->addRule('requestLimit', ts('The request limit has to be numeric.'), 'numeric', null, 'client');
        $this->addRule('requestLimit', ts('The request limit has to be numeric.'), 'numeric');
    }

    /**
     * Build form
     */
    public function buildQuickForm()
    {
        // get the current configuration object
        $config = $this->config->get();
        // Add form elements
        $this->add('text', 'clientId', ts('Client ID'), [], true);
        $this->add('text', 'clientSecret', ts('Client secret'), [], true);
        $this->add('text', 'paypalHost', ts('Paypal environment host'), [], true);
        $this->add('datepicker', 'startDate', ts('Transactions later than'), [], true, ['minDate' => date('Y-m-d', strtotime('-3 years', strtotime(date('Y-m-d')))), 'maxDate' => date('Y-m-d')]);
        $this->add('text', 'importLimit', ts('Import limit'), [], true);
        $this->add('text', 'requestLimit', ts('Request limit'), [], true);
        $this->add('select', 'paymentInstrumentId', ts( 'Payment method' ), [''=>ts( '- select -' )] + CRM_Contribute_PseudoConstant::paymentInstrument(), true);
        $this->add('select', 'financialTypeId', ts( 'Financial Type' ), [''=>ts( '- select -' )] + CRM_Contribute_PseudoConstant::financialType(), true);
        // checkbox for triggering the state change of the application.
        // - if current state is do-nothing, start action, if set bumps the state to import-init
        if ($config['state'] == 'do-nothing') {
            $this->add('checkbox', 'action', ts('Start Import'), [], false);
        }
        // - if the current state is import(-init)? sync stop action, if set bumps the state back to do-nothing
        if ($config['state'] == 'import' || $config['state'] == 'import-init' || $config['state'] == 'sync') {
            $this->add('checkbox', 'action', ts('Stop Import'), [], false);
        }
        // - if the state error, confirm action, it sets the state back to do-nothing
        if ($config['state'] == 'error') {
            $this->add('checkbox', 'action', ts('Confirm Error'), [], false);
        }

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
            'client-id' => $this->_submitValues['clientId'],
            'client-secret' => $this->_submitValues['clientSecret'],
            'paypal-host' => $this->_submitValues['paypalHost'],
            'start-date' => $this->_submitValues['startDate'],
            'import-limit' => intval($this->_submitValues['importLimit'], 10),
            'request-limit' => intval($this->_submitValues['requestLimit'], 10),
            'payment-instrument-id' => intval($this->_submitValues['paymentInstrumentId'], 10),
            'financial-type-id' => intval($this->_submitValues['financialTypeId'], 10),
        ];
        try {
            if (!$this->config->updateSettings($submitData)) {
                CRM_Core_Session::setStatus(ts('Error during save process'), 'Paypal Importer', 'error');
            } else {
                CRM_Core_Session::setStatus(ts('Data has been updated.'), 'Paypal Importer', 'success', ['expires' => 5000,]);
                // on case of the action is selected, handle it. if the current state is do-nothing, push it to import-init
                // else setup the do-nothing state.
                if ($this->_submitValues['action']) {
                    // get the current configuration object
                    $this->config->load();
                    $config = $this->config->get();
                    if ($config['state'] == 'do-nothing') {
                        $this->config->updateState('import-init');
                    } else {
                        $this->config->updateState('do-nothing');
                    }
                }
            }
        } catch (CRM_Core_Exception $e) {
            CRM_Core_Session::setStatus(ts($e->getMessage()), 'Paypal Importer', 'error');
        }
        parent::postProcess();
    }
}

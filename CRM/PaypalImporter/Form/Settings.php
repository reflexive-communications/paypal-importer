<?php

use Civi\PaypalImporter\Config;
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
     * @var Config
     */
    private $config;

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function preProcess(): void
    {
        // Get current settings
        $this->config = new Config(E::LONG_NAME);
        $this->config->load();
    }

    /**
     * @return array
     * @throws \CRM_Core_Exception
     */
    public function setDefaultValues(): array
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
        $this->_defaults['tagId'] = $config['settings']['tag-id'];
        $this->_defaults['groupId'] = $config['settings']['group-id'];
        $this->_defaults['actionCheckbox'] = 0;

        return $this->_defaults;
    }

    /**
     * @return void
     */
    public function addRules(): void
    {
        $this->addRule('importLimit', ts('The import limit has to be numeric.'), 'numeric', null, 'client');
        $this->addRule('importLimit', ts('The import limit has to be numeric.'), 'numeric');
        $this->addRule('requestLimit', ts('The request limit has to be numeric.'), 'numeric', null, 'client');
        $this->addRule('requestLimit', ts('The request limit has to be numeric.'), 'numeric');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function buildQuickForm(): void
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
        $this->add('select', 'paymentInstrumentId', ts('Payment method'), ['' => ts('- select -')] + CRM_Contribute_BAO_Contribution::buildOptions('payment_instrument_id', 'search'), true);
        $this->add('select', 'financialTypeId', ts('Financial Type'), ['' => ts('- select -')] + CRM_Contribute_BAO_Contribution::buildOptions('financial_type_id', 'search'), true);
        $this->add('select', 'tagId', ts('Tag contact'), [0 => ts('- select -')] + CRM_Core_BAO_EntityTag::buildOptions('tag_id', 'search', ['entity_table' => 'civicrm_contact']));
        $this->add('select', 'groupId', ts('Group contact'), [0 => ts('- select -')] + CRM_Contact_BAO_GroupContact::buildOptions('group_id', 'search'));
        // checkbox for triggering the state change of the application.
        // - if current state is do-nothing, start action, if set bumps the state to import-init
        if ($config['state'] == 'do-nothing') {
            $this->add('checkbox', 'actionCheckbox', ts('Start Import'));
        }
        // - if the current state is import(-init)? sync stop action, if set bumps the state back to do-nothing
        if ($config['state'] == 'import' || $config['state'] == 'import-init' || $config['state'] == 'sync') {
            $this->add('checkbox', 'actionCheckbox', ts('Stop Import'));
        }
        // - if the state error, confirm action, it sets the state back to do-nothing
        if ($config['state'] == 'error') {
            $this->add('checkbox', 'actionCheckbox', ts('Confirm Error'));
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
        // export current state and the log to the template
        $this->assign('currentState', $config['state']);
        if (isset($config['import-stats']['new-user'])) {
            $this->assign('lastStatsUser', $config['import-stats']['new-user']);
        }
        if (isset($config['import-stats']['transaction'])) {
            $this->assign('lastStatsTransaction', $config['import-stats']['transaction']);
        }
        if (isset($config['import-stats']['errors'])) {
            $this->assign('lastStatsErrors', $config['import-stats']['errors']);
        }
        $this->assign('lastLogError', $config['import-error']);
        $this->assign('reloadPage', CRM_Utils_System::url('civicrm/contribute/paypal-import'));
    }

    /**
     * @return void
     */
    public function postProcess(): void
    {
        $submitData = [
            'client-id' => $this->_submitValues['clientId'],
            'client-secret' => $this->_submitValues['clientSecret'],
            'paypal-host' => $this->_submitValues['paypalHost'],
            'start-date' => $this->_submitValues['startDate'],
            'import-limit' => intval($this->_submitValues['importLimit']),
            'request-limit' => intval($this->_submitValues['requestLimit']),
            'payment-instrument-id' => intval($this->_submitValues['paymentInstrumentId']),
            'financial-type-id' => intval($this->_submitValues['financialTypeId']),
            'tag-id' => intval($this->_submitValues['tagId']),
            'group-id' => intval($this->_submitValues['groupId']),
        ];
        try {
            if (!$this->config->updateSettings($submitData)) {
                CRM_Core_Session::setStatus(ts('Error during save process'), 'Paypal Importer', 'error');
            } else {
                CRM_Core_Session::setStatus(ts('Data has been updated.'), 'Paypal Importer', 'success', ['expires' => 5000]);
                // on case of the action is selected, handle it. if the current state is do-nothing, push it to import-init
                // else setup the do-nothing state.
                if ($this->_submitValues['actionCheckbox']) {
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

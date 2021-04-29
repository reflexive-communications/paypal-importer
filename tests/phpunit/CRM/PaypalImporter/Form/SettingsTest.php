<?php

use CRM_PaypalImporter_ExtensionUtil as E;

/**
 * Settings form tests.
 *
 * @group headless
 */
class CRM_PaypalImporter_Form_SettingsTest extends CRM_PaypalImporter_HeadlessBase
{
    const TEST_SETTINGS = [
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
        'import-stats' => [
        ],
        'import-error' => '',
    ];
    private function setupTestConfig()
    {
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        self::assertTrue($config->update(self::TEST_SETTINGS), 'Config update has to be successful.');
    }

    /**
     * PreProcess test case with existing config.
     * Setup test configuration then call the function.
     * It shouldn't throw exception.
     */
    public function testPreProcessExistingConfig()
    {
        $this->setupTestConfig();
        $form = new CRM_PaypalImporter_Form_Settings();
        try {
            self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        } catch (Exception $e) {
            self::fail('Shouldn\'t throw exception with valid db. '.$e->getMessage());
        }
    }

    /**
     * PreProcess test case with deleted config.
     * Setup test configuration then call the function.
     * It should throw exception.
     */
    public function testPreProcessMissingConfig()
    {
        $form = new CRM_PaypalImporter_Form_Settings();
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        $config->remove();
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage(E::LONG_NAME.'_config config invalid.');
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
    }

    /**
     * Build quick form test case.
     * Setup test configuration, preProcess then call the function.
     * It shouldn't throw exception.
     * The title should be set.
     */
    public function testBuildQuickFormNoActionState()
    {
        $this->setupTestConfig();
        $form = new CRM_PaypalImporter_Form_Settings();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        try {
            self::assertEmpty($form->buildQuickForm());
        } catch (Exception $e) {
            self::fail('It shouldn\'t throw exception. '.$e->getMessage());
        }
        self::assertSame('Paypal data importer', $form->getTitle(), 'Invalid form title.');
    }
    public function testBuildQuickFormImportInitState()
    {
        $this->setupTestConfig();
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        $config->updateState('import-init');
        $form = new CRM_PaypalImporter_Form_Settings();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        try {
            self::assertEmpty($form->buildQuickForm());
        } catch (Exception $e) {
            self::fail('It shouldn\'t throw exception. '.$e->getMessage());
        }
        self::assertSame('Paypal data importer', $form->getTitle(), 'Invalid form title.');
    }
    public function testBuildQuickFormImportError()
    {
        $this->setupTestConfig();
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        $config->updateState('error');
        $form = new CRM_PaypalImporter_Form_Settings();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        try {
            self::assertEmpty($form->buildQuickForm());
        } catch (Exception $e) {
            self::fail('It shouldn\'t throw exception. '.$e->getMessage());
        }
        self::assertSame('Paypal data importer', $form->getTitle(), 'Invalid form title.');
    }

    /**
     * setDefaultValues test case.
     */
    public function testSetDefaultValues()
    {
        $this->setupTestConfig();
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        $config->updateState('error');
        $form = new CRM_PaypalImporter_Form_Settings();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        $config->load();
        $cfg = $config->get();
        $expectedConfig = [
            'clientId' => $cfg['settings']['client-id'],
            'clientSecret' => $cfg['settings']['client-secret'],
            'paypalHost' => $cfg['settings']['paypal-host'],
            'startDate' => $cfg['settings']['start-date'],
            'importLimit' => $cfg['settings']['import-limit'],
            'requestLimit' => $cfg['settings']['request-limit'],
            'paymentInstrumentId' => $cfg['settings']['payment-instrument-id'],
            'financialTypeId' => $cfg['settings']['financial-type-id'],
            'action' => 0,
        ];
        self::assertSame($expectedConfig, $form->setDefaultValues());
    }

    /**
     * Add Rules test case.
     * It shouldn't throw exception.
     */
    public function testAddRules()
    {
        $this->setupTestConfig();
        $form = new CRM_PaypalImporter_Form_Settings();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->buildQuickForm());
        try {
            self::assertEmpty($form->addRules());
        } catch (Exception $e) {
            self::fail('It shouldn\'t throw exception. '.$e->getMessage());
        }
    }

    /**
     * Post Process test cases.
     */
    public function testPostProcessNoAction()
    {
        $_POST['clientId'] = 'client-id';
        $_POST['clientSecret'] = 'client-secret';
        $_POST['paypalHost'] = 'localhost';
        $_POST['startDate'] = '2020-01-01';
        $_POST['importLimit'] = 100;
        $_POST['requestLimit'] = 5;
        $_POST['paymentInstrumentId'] = 1;
        $_POST['financialTypeId'] = 1;
        $_POST['action'] = 0;
        $this->setupTestConfig();
        $form = new CRM_PaypalImporter_Form_Settings();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        try {
            self::assertEmpty($form->postProcess());
        } catch (Exception $e) {
            self::fail('It shouldn\'t throw exception. '.$e->getMessage());
        }
    }
    public function testPostProcessWithActionDefaultState()
    {
        $_POST['clientId'] = 'client-id';
        $_POST['clientSecret'] = 'client-secret';
        $_POST['paypalHost'] = 'localhost';
        $_POST['startDate'] = '2020-01-01';
        $_POST['importLimit'] = 100;
        $_POST['requestLimit'] = 5;
        $_POST['paymentInstrumentId'] = 1;
        $_POST['financialTypeId'] = 1;
        $_POST['action'] = 1;
        $this->setupTestConfig();
        $form = new CRM_PaypalImporter_Form_Settings();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        try {
            self::assertEmpty($form->postProcess());
        } catch (Exception $e) {
            self::fail('It shouldn\'t throw exception. '.$e->getMessage());
        }
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        $config->load();
        $cfg = $config->get();
        self::assertSame('import-init', $cfg['state'], 'Invalid state after start the action.');
    }
    public function testPostProcessWithActionImportState()
    {
        $_POST['clientId'] = 'client-id';
        $_POST['clientSecret'] = 'client-secret';
        $_POST['paypalHost'] = 'localhost';
        $_POST['startDate'] = '2020-01-01';
        $_POST['importLimit'] = 100;
        $_POST['requestLimit'] = 5;
        $_POST['paymentInstrumentId'] = 1;
        $_POST['financialTypeId'] = 1;
        $_POST['action'] = 1;
        $this->setupTestConfig();
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        $config->updateState('import');
        $form = new CRM_PaypalImporter_Form_Settings();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        try {
            self::assertEmpty($form->postProcess());
        } catch (Exception $e) {
            self::fail('It shouldn\'t throw exception. '.$e->getMessage());
        }
        $config = new CRM_PaypalImporter_Config(E::LONG_NAME);
        $config->load();
        $cfg = $config->get();
        self::assertSame('do-nothing', $cfg['state'], 'Invalid state after start the action.');
    }
}

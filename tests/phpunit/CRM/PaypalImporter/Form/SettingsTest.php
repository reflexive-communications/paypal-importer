<?php

use Civi\PaypalImporter\Config;
use Civi\PaypalImporter\HeadlessTestCase;
use CRM_PaypalImporter_ExtensionUtil as E;

/**
 * @group headless
 */
class CRM_PaypalImporter_Form_SettingsTest extends HeadlessTestCase
{
    public const TEST_SETTINGS = [
        'settings' => [
            'client-id' => '',
            'client-secret' => '',
            'paypal-host' => '',
            'start-date' => '',
            'import-limit' => 1,
            'financial-type-id' => '',
            'payment-instrument-id' => '',
            'request-limit' => 1,
            'tag-id' => 0,
            'group-id' => 0,
        ],
        'state' => 'do-nothing',
        'import-params' => [
        ],
        'import-stats' => [
        ],
        'import-error' => '',
    ];

    /**
     * @return void
     */
    private function setupTestConfig()
    {
        $config = new Config(E::LONG_NAME);
        self::assertTrue($config->update(self::TEST_SETTINGS), 'Config update has to be successful.');
    }

    /**
     * @return void
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
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testPreProcessMissingConfig()
    {
        $form = new CRM_PaypalImporter_Form_Settings();
        $config = new Config(E::LONG_NAME);
        $config->remove();
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage(E::LONG_NAME.'_config config invalid.');
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
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

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testBuildQuickFormImportInitState()
    {
        $this->setupTestConfig();
        $config = new Config(E::LONG_NAME);
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

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testBuildQuickFormImportError()
    {
        $this->setupTestConfig();
        $config = new Config(E::LONG_NAME);
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
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testBuildQuickFormImportStats()
    {
        $this->setupTestConfig();
        $config = new Config(E::LONG_NAME);
        $config->updateState('import-init');
        $form = new CRM_PaypalImporter_Form_Settings();
        $config->updateImportStats(['new-user' => 2, 'transaction' => 2, 'error' => ['Something went wrong.']]);
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        try {
            self::assertEmpty($form->buildQuickForm());
        } catch (Exception $e) {
            self::fail('It shouldn\'t throw exception. '.$e->getMessage());
        }
        self::assertSame('Paypal data importer', $form->getTitle(), 'Invalid form title.');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testSetDefaultValues()
    {
        $this->setupTestConfig();
        $config = new Config(E::LONG_NAME);
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
            'tagId' => $cfg['settings']['tag-id'],
            'groupId' => $cfg['settings']['group-id'],
            'actionCheckbox' => 0,
        ];
        self::assertSame($expectedConfig, $form->setDefaultValues());
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
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
     * @return void
     * @throws \CRM_Core_Exception
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
        $_POST['actionCheckbox'] = 0;
        $_POST['tagId'] = 0;
        $_POST['groupId'] = 0;
        $this->setupTestConfig();
        $form = new CRM_PaypalImporter_Form_Settings();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        try {
            self::assertEmpty($form->postProcess());
        } catch (Exception $e) {
            self::fail('It shouldn\'t throw exception. '.$e->getMessage());
        }
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
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
        $_POST['actionCheckbox'] = 1;
        $_POST['tagId'] = 0;
        $_POST['groupId'] = 0;
        $this->setupTestConfig();
        $form = new CRM_PaypalImporter_Form_Settings();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        try {
            self::assertEmpty($form->postProcess());
        } catch (Exception $e) {
            self::fail('It shouldn\'t throw exception. '.$e->getMessage());
        }
        $config = new Config(E::LONG_NAME);
        $config->load();
        $cfg = $config->get();
        self::assertSame('import-init', $cfg['state'], 'Invalid state after start the action.');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
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
        $_POST['actionCheckbox'] = 1;
        $_POST['tagId'] = 0;
        $_POST['groupId'] = 0;
        $this->setupTestConfig();
        $config = new Config(E::LONG_NAME);
        $config->updateState('import');
        $form = new CRM_PaypalImporter_Form_Settings();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        try {
            self::assertEmpty($form->postProcess());
        } catch (Exception $e) {
            self::fail('It shouldn\'t throw exception. '.$e->getMessage());
        }
        $config = new Config(E::LONG_NAME);
        $config->load();
        $cfg = $config->get();
        self::assertSame('do-nothing', $cfg['state'], 'Invalid state after start the action.');
    }
}

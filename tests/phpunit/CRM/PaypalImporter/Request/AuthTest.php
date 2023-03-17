<?php

use Civi\PaypalImporter\HeadlessTestCase;

/**
 * @group headless
 */
class CRM_PaypalImporter_Request_AuthTest extends HeadlessTestCase
{
    const TEST_DATA = [
        [
            'host' => 'localhost',
            'clientId' => '',
            'clientSecret' => '',
        ],
        [
            'host' => 'localhost',
            'clientId' => '',
            'clientSecret' => 'blah',
        ],
        [
            'host' => 'localhost',
            'clientId' => 'asd',
            'clientSecret' => 'blah',
        ],
        [
            'host' => 'localhost',
            'clientId' => 'asd',
            'clientSecret' => '',
        ],
    ];

    const EXPECTED_OPTIONS = [
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => 'PayPal-Civicrm-Importer',
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_SSL_VERIFYPEER => 1,
        CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
        CURLOPT_FOLLOWLOCATION => true,
    ];

    const EXPECTED_DATA = [
        'grant_type' => 'client_credentials',
    ];

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetHost()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new CRM_PaypalImporter_Request_Auth($settings['host'], $settings['clientId'], $settings['clientSecret']);
            self::assertSame($settings['host'], $req->getHost(), 'Invalid host configuration has been returned.');
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetEndpoint()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new CRM_PaypalImporter_Request_Auth($settings['host'], $settings['clientId'], $settings['clientSecret']);
            self::assertSame(CRM_PaypalImporter_Request_Auth::ENDPOINT, $req->getEndpoint(), 'Invalid endpoint configuration has been returned.');
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetOptions()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new CRM_PaypalImporter_Request_Auth($settings['host'], $settings['clientId'], $settings['clientSecret']);
            self::assertSame(self::EXPECTED_OPTIONS, $req->getOptions(), 'Invalid options configuration has been returned.');
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetRequestHeaders()
    {
        $expectedHeaderBase = [
            'Accept: '.CRM_PaypalImporter_Request_Base::ACCEPT_HEADER,
            'Accept-Language: '.CRM_PaypalImporter_Request_Base::ACCEPT_LANGUAGE_HEADER,
            'Content-Type: '.CRM_PaypalImporter_Request_Auth::CONTENT_TYPE_HEADER,
            'Authorization: Basic ',
        ];
        foreach (self::TEST_DATA as $settings) {
            $expectedHeaderBase[3] = 'Authorization: Basic '.base64_encode($settings['clientId'].':'.$settings['clientSecret']);
            $req = new CRM_PaypalImporter_Request_Auth($settings['host'], $settings['clientId'], $settings['clientSecret']);
            self::assertSame($expectedHeaderBase, $req->getRequestHeaders(), 'Invalid headers configuration has been returned.');
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetRequestData()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new CRM_PaypalImporter_Request_Auth($settings['host'], $settings['clientId'], $settings['clientSecret']);
            self::assertSame(self::EXPECTED_DATA, $req->getRequestData(), 'Invalid data configuration has been returned.');
        }
    }
}

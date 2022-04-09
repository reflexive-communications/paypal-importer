<?php

/**
 * Unit tests for the transactions request class.
 */
class CRM_PaypalImporter_Request_TransactionsTest extends CRM_PaypalImporter_Request_TestBase
{
    const TEST_DATA = [
        [
            'host' => 'localhost',
            'token' => '',
            'data' => [],
        ],
        [
            'host' => 'localhost',
            'token' => 'asdfg',
            'data' => [],
        ],
        [
            'host' => 'localhost',
            'token' => '',
            'data' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
        ],
        [
            'host' => 'localhost',
            'token' => 'test-token',
            'data' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
        ],
    ];
    const EXPECTED_OPTIONS = [
        CURLOPT_SSLVERSION => 6,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => 'PayPal-Civicrm-Importer',
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_SSL_VERIFYPEER => 1,
        CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
        CURLOPT_FOLLOWLOCATION => true,
    ];

    /*
     * Tests for the setup of the Requests
     * with the getter methods.
     */
    public function testGetHost()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new CRM_PaypalImporter_Request_Transactions($settings['host'], $settings['token'], $settings['data']);
            self::assertSame($settings['host'], $req->getHost(), 'Invalid host configuration has been returned.');
        }
    }
    public function testGetEndpoint()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new CRM_PaypalImporter_Request_Transactions($settings['host'], $settings['token'], $settings['data']);
            self::assertSame(CRM_PaypalImporter_Request_Transactions::ENDPOINT, $req->getEndpoint(), 'Invalid endpoint configuration has been returned.');
        }
    }
    public function testGetOptions()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new CRM_PaypalImporter_Request_Transactions($settings['host'], $settings['token'], $settings['data']);
            self::assertSame(self::EXPECTED_OPTIONS, $req->getOptions(), 'Invalid options configuration has been returned.');
        }
    }
    public function testGetRequestHeaders()
    {
        $expectedHeaderBase = [
            'Accept: '. CRM_PaypalImporter_Request_Base::ACCEPT_HEADER,
            'Accept-Language: '. CRM_PaypalImporter_Request_Base::ACCEPT_LANGUAGE_HEADER,
            'Content-Type: '. CRM_PaypalImporter_Request_Transactions::CONTENT_TYPE_HEADER,
            'Authorization: Basic ',
        ];
        foreach (self::TEST_DATA as $settings) {
            $expectedHeaderBase[3] = 'Authorization: Bearer '.$settings['token'];
            $req = new CRM_PaypalImporter_Request_Transactions($settings['host'], $settings['token'], $settings['data']);
            self::assertSame($expectedHeaderBase, $req->getRequestHeaders(), 'Invalid headers configuration has been returned.');
        }
    }
    public function testGetRequestData()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new CRM_PaypalImporter_Request_Transactions($settings['host'], $settings['token'], $settings['data']);
            self::assertSame($settings['data'], $req->getRequestData(), 'Invalid data configuration has been returned.');
        }
    }
}

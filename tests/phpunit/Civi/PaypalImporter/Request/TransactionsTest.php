<?php

namespace Civi\PaypalImporter\Request;

use Civi\PaypalImporter\HeadlessTestCase;

/**
 * @group headless
 */
class TransactionsTest extends HeadlessTestCase
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
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => 'PayPal-Civicrm-Importer',
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_SSL_VERIFYPEER => 1,
        CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
        CURLOPT_FOLLOWLOCATION => true,
    ];

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetHost()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new Transactions($settings['host'], $settings['token'], $settings['data']);
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
            $req = new Transactions($settings['host'], $settings['token'], $settings['data']);
            self::assertSame(Transactions::ENDPOINT, $req->getEndpoint(), 'Invalid endpoint configuration has been returned.');
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetOptions()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new Transactions($settings['host'], $settings['token'], $settings['data']);
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
            'Accept: '.Base::ACCEPT_HEADER,
            'Accept-Language: '.Base::ACCEPT_LANGUAGE_HEADER,
            'Content-Type: '.Transactions::CONTENT_TYPE_HEADER,
            'Authorization: Basic ',
        ];
        foreach (self::TEST_DATA as $settings) {
            $expectedHeaderBase[3] = 'Authorization: Bearer '.$settings['token'];
            $req = new Transactions($settings['host'], $settings['token'], $settings['data']);
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
            $req = new Transactions($settings['host'], $settings['token'], $settings['data']);
            self::assertSame($settings['data'], $req->getRequestData(), 'Invalid data configuration has been returned.');
        }
    }
}

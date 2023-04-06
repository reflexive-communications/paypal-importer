<?php

namespace Civi\PaypalImporter\Request;

use Civi\PaypalImporter\HeadlessTestCase;

/**
 * @group headless
 */
class AuthTest extends HeadlessTestCase
{
    public const TEST_DATA = [
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

    public const EXPECTED_OPTIONS = [
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => 'PayPal-Civicrm-Importer',
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_SSL_VERIFYPEER => 1,
        CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
        CURLOPT_FOLLOWLOCATION => true,
    ];

    public const EXPECTED_DATA = [
        'grant_type' => 'client_credentials',
    ];

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetHost()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new Auth($settings['host'], $settings['clientId'], $settings['clientSecret']);
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
            $req = new Auth($settings['host'], $settings['clientId'], $settings['clientSecret']);
            self::assertSame(Auth::ENDPOINT, $req->getEndpoint(), 'Invalid endpoint configuration has been returned.');
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetOptions()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new Auth($settings['host'], $settings['clientId'], $settings['clientSecret']);
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
            'Content-Type: '.Auth::CONTENT_TYPE_HEADER,
            'Authorization: Basic ',
        ];
        foreach (self::TEST_DATA as $settings) {
            $expectedHeaderBase[3] = 'Authorization: Basic '.base64_encode($settings['clientId'].':'.$settings['clientSecret']);
            $req = new Auth($settings['host'], $settings['clientId'], $settings['clientSecret']);
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
            $req = new Auth($settings['host'], $settings['clientId'], $settings['clientSecret']);
            self::assertSame(self::EXPECTED_DATA, $req->getRequestData(), 'Invalid data configuration has been returned.');
        }
    }
}

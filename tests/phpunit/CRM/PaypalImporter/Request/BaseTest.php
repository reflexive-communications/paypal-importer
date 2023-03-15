<?php

use Civi\PaypalImporter\HeadlessTestCase;

/**
 * @group headless
 */
class CRM_PaypalImporter_Request_BaseTest extends HeadlessTestCase
{
    const TEST_DATA = [
        [
            'host' => 'localhost',
            'endpoint' => '/api.php',
            'options' => [],
            'headers' => [],
            'data' => [],
        ],
        [
            'host' => 'localhost',
            'endpoint' => '/api.php',
            'options' => [
                CURLOPT_SSLVERSION => 6,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_RETURNTRANSFER => true,
            ],
            'headers' => [],
            'data' => [],
        ],
        [
            'host' => 'localhost',
            'endpoint' => '/api.php',
            'options' => [
                CURLOPT_SSLVERSION => 6,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_RETURNTRANSFER => true,
            ],
            'headers' => [
                'header1: value1',
                'header2: value2',
            ],
            'data' => [],
        ],
        [
            'host' => 'localhost',
            'endpoint' => '/api.php',
            'options' => [
                CURLOPT_SSLVERSION => 6,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_RETURNTRANSFER => true,
            ],
            'headers' => [
                'header1: value1',
                'header2: value2',
            ],
            'data' => [
                'key1' => 'value 1',
                'key2' => 3,
            ],
        ],
    ];

    /*
     * Tests for the getter functions.
     */
    public function testGetHost()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new CRM_PaypalImporter_Request_Base($settings['host'], $settings['endpoint'], $settings['options'], $settings['headers'], $settings['data']);
            self::assertSame($settings['host'], $req->getHost(), 'Invalid host configuration has been returned.');
        }
    }

    public function testGetEndpoint()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new CRM_PaypalImporter_Request_Base($settings['host'], $settings['endpoint'], $settings['options'], $settings['headers'], $settings['data']);
            self::assertSame($settings['endpoint'], $req->getEndpoint(), 'Invalid endpoint configuration has been returned.');
        }
    }

    public function testGetOptions()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new CRM_PaypalImporter_Request_Base($settings['host'], $settings['endpoint'], $settings['options'], $settings['headers'], $settings['data']);
            self::assertSame($settings['options'], $req->getOptions(), 'Invalid options configuration has been returned.');
        }
    }

    public function testGetRequestHeaders()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new CRM_PaypalImporter_Request_Base($settings['host'], $settings['endpoint'], $settings['options'], $settings['headers'], $settings['data']);
            self::assertSame($settings['headers'], $req->getRequestHeaders(), 'Invalid headers configuration has been returned.');
        }
    }

    public function testGetRequestData()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new CRM_PaypalImporter_Request_Base($settings['host'], $settings['endpoint'], $settings['options'], $settings['headers'], $settings['data']);
            self::assertSame($settings['data'], $req->getRequestData(), 'Invalid data configuration has been returned.');
        }
    }

    /*
     * Tests the request functions.
     */
    public function testGet()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new CRM_PaypalImporter_Request_Base($settings['host'], $settings['endpoint'], $settings['options'], $settings['headers'], $settings['data']);
            self::assertEmpty($req->get());
        }
    }

    public function testPost()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new CRM_PaypalImporter_Request_Base($settings['host'], $settings['endpoint'], $settings['options'], $settings['headers'], $settings['data']);
            self::assertEmpty($req->post());
        }
    }

    /*
     * Tests the getResponse functions.
     */
    public function testGetResponse()
    {
        foreach (self::TEST_DATA as $settings) {
            $req = new CRM_PaypalImporter_Request_Base($settings['host'], $settings['endpoint'], $settings['options'], $settings['headers'], $settings['data']);
            self::assertEmpty($req->post());
            $resp = $req->getResponse();
            self::assertSame(403, $resp['code'], 'Invalid status code has been returned.');
        }
    }
}

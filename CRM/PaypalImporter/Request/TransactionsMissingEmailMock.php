<?php

class CRM_PaypalImporter_Request_TransactionsMissingEmailMock
{
    public const TRANSACTION_ID = '5TY05013RG002845M';

    /**
     * Default Constructor
     *
     * @param string $host
     * @param string $accessToken
     *
     * @throws Exception
     * */
    public function __construct(string $host, string $accessToken, array $searchParams = [])
    {
    }

    /**
     * Performs a post request.
     */
    public function post()
    {
    }

    /**
     * Performs a get request.
     */
    public function get()
    {
    }

    /**
     * Returns the status code, headers, and data of the last executed request.
     *
     * @return array
     */
    public function getResponse(): array
    {
        return [
            'code' => 200,
            'headers' => [],
            'data' => '{"transaction_details":[{"transaction_info":{"transaction_id":"'.self::TRANSACTION_ID
                .'","transaction_initiation_date":"2014-07-11T04:03:52+0000","transaction_amount":{"value":"1000","currency_code":"USD"}},"payer_info":{}}], "total_pages":1, "last_refreshed_datetime": "'
                .date(DATE_ISO8601, strtotime("now -12 hours")).'"}',
        ];
    }
}

<?php

class CRM_PaypalImporter_Request_TransactionsPagingMock
{
    public const TRANSACTION_IDS = ['5TY05013RG002848M', '5TY05013RG002849M'];

    /**
     * Default Constructor
     *
     * @param string $host
     * @param string $accessToken
     * @param array $searchParams
     */
    public function __construct(string $host, string $accessToken, array $searchParams = [])
    {
    }

    /**
     * Performs a post request.
     */
    public function post(): void
    {
    }

    /**
     * Performs a get request.
     */
    public function get(): void
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
            'data' => '{"transaction_details":[{"transaction_info":{"transaction_id":"'.self::TRANSACTION_IDS[0].'","transaction_initiation_date":"'.date(DATE_ISO8601, strtotime("now -55 days"))
                .'","transaction_amount":{"value":"1000.00","currency_code":"HUF"},"fee_amount":{"value":"-100.00","currency_code":"HUF"}, "transaction_status":"S"},"payer_info":{"email_address": "consumer1@example.com", "payer_name": {"given_name": "test","surname": "consumer1"}},"cart_info": {"item_details": [{"item_name": "Item1 - radio"}]}},{"transaction_info":{"transaction_id":"'
                .self::TRANSACTION_IDS[1].'","transaction_initiation_date":"'.date(DATE_ISO8601, strtotime("now -50 days"))
                .'","transaction_amount":{"value":"1000.00","currency_code":"HUF"},"fee_amount":{"value":"-100.00","currency_code":"HUF"}, "transaction_status":"S"},"payer_info":{"email_address": "consumer2@example.com", "payer_name": {"given_name": "test","surname": "consumer2"}},"cart_info": {"item_details": [{"item_name": "Item1 - radio"}]}}], "total_pages":3, "last_refreshed_datetime": "'
                .date(DATE_ISO8601, strtotime("now -12 hours")).'"}',
        ];
    }
}

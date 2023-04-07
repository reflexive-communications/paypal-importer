<?php

namespace Civi\PaypalImporter\Request;

class TransactionsNoTransactionMock
{
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
            'data' => '{"transaction_details":[], "total_pages":0, "last_refreshed_datetime": "'.date(DATE_ISO8601, strtotime('now -12 hours')).'"}',
        ];
    }
}

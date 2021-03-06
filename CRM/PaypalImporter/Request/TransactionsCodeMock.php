<?php

class CRM_PaypalImporter_Request_TransactionsCodeMock
{
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
            'code' => 401,
            'headers' => [],
            'data' => '',
        ];
    }
}

<?php

class CRM_PaypalImporter_Request_AuthCodeMock
{
    /**
     * Default Constructor
     *
     * @param string $host
     * @param string $clientId
     * @param string $clientSecret
     * */
    public function __construct(string $host, string $clientId, string $clientSecret)
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

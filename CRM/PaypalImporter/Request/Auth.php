<?php

class CRM_PaypalImporter_Request_Auth extends CRM_PaypalImporter_Request_Base
{
    public const ENDPOINT = '/v1/oauth2/token';

    public const CONTENT_TYPE_HEADER = 'multipart/form-data';

    /**
     * Default Constructor
     *
     * @param string $host
     * @param string $clientId
     * @param string $clientSecret
     *
     * @throws Exception
     * */
    public function __construct(string $host, string $clientId, string $clientSecret)
    {
        parent::__construct($host, self::ENDPOINT, parent::curlOptions(), self::curlHeaders($clientId, $clientSecret), self::curlData());
    }

    /**
     * Curl headers for the auth requests.
     *
     * @param string $clientId
     * @param string $clientSecret
     *
     * @return array
     * */
    private static function curlHeaders(string $clientId, string $clientSecret): array
    {
        return [
            'Accept: '.parent::ACCEPT_HEADER,
            'Accept-Language: '.parent::ACCEPT_LANGUAGE_HEADER,
            'Content-Type: '.self::CONTENT_TYPE_HEADER,
            'Authorization: Basic '.base64_encode($clientId.':'.$clientSecret),
        ];
    }

    /**
     * Curl data for the auth requests.
     *
     * @return array
     * */
    private static function curlData(): array
    {
        return [
            'grant_type' => 'client_credentials',
        ];
    }
}

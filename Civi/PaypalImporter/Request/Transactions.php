<?php

namespace Civi\PaypalImporter\Request;

class Transactions extends Base
{
    public const ENDPOINT = '/v1/reporting/transactions';

    public const CONTENT_TYPE_HEADER = 'application/json';

    /**
     * Default Constructor
     *
     * @param string $host
     * @param string $accessToken
     * @param array $searchParams
     *
     * @throws \Exception
     */
    public function __construct(string $host, string $accessToken, array $searchParams = [])
    {
        parent::__construct($host, self::ENDPOINT, parent::curlOptions(), self::curlHeaders($accessToken), $searchParams);
    }

    /**
     * Curl headers for the auth requests.
     *
     * @param string $accessToken
     *
     * @return array
     */
    private static function curlHeaders(string $accessToken): array
    {
        return [
            'Accept: '.parent::ACCEPT_HEADER,
            'Accept-Language: '.parent::ACCEPT_LANGUAGE_HEADER,
            'Content-Type: '.self::CONTENT_TYPE_HEADER,
            'Authorization: Bearer '.$accessToken,
        ];
    }
}

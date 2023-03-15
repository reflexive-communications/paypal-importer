<?php

/**
 * HTTP request class. It is responsible for the curl call. Based on the deprecated sdk.
 * https://github.com/paypal/PayPal-PHP-SDK/blob/1a2ed767bb09374a8a222069930e94fccf99009e/lib/PayPal/Core/PayPalHttpConnection.php
 */
class CRM_PaypalImporter_Request_Base
{
    public const ACCEPT_HEADER = 'application/json';

    public const ACCEPT_LANGUAGE_HEADER = 'en_US';

    /**
     * @var string hostname
     */
    private $host;

    /**
     * @var string endpoint
     */
    private $endpoint;

    /**
     * @var array curl options
     */
    private $options;

    /**
     * @var array request headers
     */
    private $requestHeaders;

    /**
     * @var array request data
     */
    private $requestData;

    /**
     * @var array response headers
     */
    private $responseHeaders;

    /**
     * @var array response data
     */
    private $responseData;

    /**
     * @var int response status code
     */
    private $responseStatusCode;

    /**
     * Default Constructor
     *
     * @param string $host
     * @param string $endpoint
     * @param array $options
     * @param array $headers
     * @param array $data
     *
     * @throws Exception
     */
    public function __construct(string $host, string $endpoint = "", array $options = [], array $headers = [], array $data = [])
    {
        if (!function_exists("curl_init")) {
            CRM_PaypalImporter_Upgrader::logError("Curl module is not available on this system");
            throw new Exception("Curl module is not available on this system");
        }
        $this->host = $host;
        $this->endpoint = $endpoint;
        $this->options = $options;
        $this->requestHeaders = $headers;
        $this->requestData = $data;
    }

    /**
     * Parses the response headers.
     *
     * @param resource $ch
     * @param string $data
     *
     * @return int
     */
    protected function parseResponseHeaders($ch, $data): int
    {
        $trimmedData = trim($data);
        if (strlen($trimmedData) == 0) {
            return strlen($data);
        }

        // Added condition to ignore extra header which dont have colon ( : )
        if (strpos($trimmedData, ":") == false) {
            return strlen($data);
        }

        [$key, $value] = explode(":", $trimmedData, 2);

        $key = trim($key);
        $value = trim($value);

        // This will skip over the HTTP Status Line and any other lines
        // that don't look like header lines with values
        if (strlen($key) > 0 && strlen($value) > 0) {
            // This is actually a very basic way of looking at response headers
            // and may miss a few repeated headers with different (appended)
            // values but this should work for debugging purposes.
            $this->responseHeaders[$key] = $value;
        }

        return strlen($data);
    }

    /**
     * Curl options for the requests.
     *
     * @return array
     */
    protected static function curlOptions(): array
    {
        return [
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,    // maximum number of seconds to allow cURL functions to execute
            CURLOPT_USERAGENT => 'PayPal-Civicrm-Importer',
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => 1,
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2, // Minimum TLSv1.2
            CURLOPT_FOLLOWLOCATION => true,
        ];
    }

    /**
     * Performs a post request.
     */
    public function post()
    {
        //Initialize Curl Options
        $ch = curl_init($this->host.$this->endpoint);
        curl_setopt_array($ch, $this->options);
        curl_setopt($ch, CURLOPT_URL, $this->host.$this->endpoint);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->requestHeaders);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->requestData));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, [$this, 'parseResponseHeaders']);
        //Execute Curl Request
        $this->responseData = curl_exec($ch);
        //Retrieve Response Status
        $this->responseStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //Close the curl request
        curl_close($ch);
    }

    /**
     * Performs a get request.
     */
    public function get()
    {
        //Initialize Curl Options
        $ch = curl_init();
        curl_setopt_array($ch, $this->options);
        $endpoint = $this->host.$this->endpoint;
        if (count($this->requestData) > 0) {
            $endpoint .= '?'.http_build_query($this->requestData);
        }
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->requestHeaders);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, [$this, 'parseResponseHeaders']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        //Execute Curl Request
        $this->responseData = curl_exec($ch);
        //Retrieve Response Status
        $this->responseStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //Close the curl request
        curl_close($ch);
    }

    /**
     * Returns the status code, headers, and data of the last executed request.
     *
     * @return array
     */
    public function getResponse(): array
    {
        return [
            'code' => $this->responseStatusCode,
            'headers' => $this->responseHeaders,
            'data' => $this->responseData,
        ];
    }

    /**
     * Returns the host.
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Returns the endpoint.
     *
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * Returns the options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Returns the requestHeaders.
     *
     * @return array
     */
    public function getRequestHeaders(): array
    {
        return $this->requestHeaders;
    }

    /**
     * Returns the requestData.
     *
     * @return array
     */
    public function getRequestData(): array
    {
        return $this->requestData;
    }
}

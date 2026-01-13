<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\Api;

use Piwik\Config;
use Piwik\Http;
use Piwik\Plugins\Marketplace\Api\Service\Exception;

/**
 *
 */
class Service
{
    public const CACHE_TIMEOUT_IN_SECONDS = 1200;
    public const HTTP_REQUEST_TIMEOUT = 60;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var null|string
     */
    private $accessToken;

    /**
     * API version to use on the Marketplace
     * @var string
     */
    private $version = '2.0';

    public function __construct($domain)
    {
        $this->domain = $domain;
    }

    public function authenticate(
        #[\SensitiveParameter]
        $accessToken
    ) {
        if (empty($accessToken)) {
            $this->accessToken = null;
        } elseif (ctype_alnum($accessToken)) {
            $this->accessToken = $accessToken;
        }
    }

    /**
     * The API version that will be used on the Marketplace.
     * @return string eg 2.0
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Returns the currently set access token
     * @return null|string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function hasAccessToken()
    {
        return !empty($this->accessToken);
    }

    /**
     * Downloads data from the given URL via a POST request. If a destination path is given, the downloaded data
     * will be stored in the given path and returned otherwise.
     *
     * Make sure to call {@link authenticate()} to download paid plugins.
     *
     * @param string $url An absolute URL to the marketplace including domain.
     * @param null|string $destinationPath
     * @param null|int $timeout Defaults to 60 seconds see {@link self::HTTP_REQUEST_METHOD}
     * @param null|array $postData eg array('email' => 'user@example.org')
     * @param bool $getExtendedInfo Return the extended response info for the HTTP request.
     * @return bool|string Returns the downloaded data or true if a destination path was given.
     * @throws \Exception
     */
    public function download(
        $url,
        $destinationPath = null,
        $timeout = null,
        ?array $postData = null,
        bool $getExtendedInfo = false
    ) {
        $method = Http::getTransportMethod();

        if (!isset($timeout)) {
            $timeout = static::HTTP_REQUEST_TIMEOUT;
        }

        if ($this->accessToken) {
            if (!is_array($postData)) {
                $postData = [];
            }

            $postData['access_token'] = $this->accessToken;
        }

        $file = Http::ensureDestinationDirectoryExists($destinationPath);

        return Http::sendHttpRequestBy(
            $method,
            $url,
            $timeout,
            $userAgent = null,
            $destinationPath,
            $file,
            $followDepth = 0,
            $acceptLanguage = false,
            $acceptInvalidSslCertificate = false,
            $byteRange = false,
            $getExtendedInfo,
            $httpMethod = 'POST',
            $httpUsername = null,
            $httpPassword = null,
            $postData
        );
    }

    /**
     * Executes the given API action on the Marketplace using the given params and returns the result.
     *
     * Make sure to call {@link authenticate()} to download paid plugins.
     *
     * @param string $action eg 'plugins', 'plugins/$pluginName/info', ...
     * @param array $params eg array('sort' => 'alpha')
     * @param null|array $postData eg array('email' => 'user@example.org')
     * @param bool $getExtendedInfo Return the extended response info for the HTTP request.
     * @param bool $throwOnApiError Throw if an error was returned from the API or return the result.
     *                              Will always throw if an HTTP error occurred (unreadable response).
     * @return mixed
     * @throws Service\Exception
     */
    public function fetch(
        $action,
        $params,
        ?array $postData = null,
        bool $getExtendedInfo = false,
        bool $throwOnApiError = true
    ) {
        $endpoint = sprintf('%s/api/%s/', $this->domain, $this->version);

        $query = Http::buildQuery($params);
        $url   = sprintf('%s%s?%s', $endpoint, $action, $query);

        $response = $this->download($url, null, null, $postData, true);
        $result = $response['data'] ?? null;

        if (null === $result) {
            throw new Service\Exception(
                'There was an error reading the response from the Marketplace. Please try again later.',
                Service\Exception::HTTP_ERROR
            );
        }

        if ('' !== $result) {
            $result = json_decode($result, true);

            if (null === $result) {
                throw new Service\Exception(
                    'There was an error reading the response from the Marketplace. Please try again later.',
                    Service\Exception::HTTP_ERROR
                );
            }

            if ($throwOnApiError && !empty($result['error'])) {
                throw new Service\Exception($result['error'], Service\Exception::API_ERROR);
            }
        }

        if (!$getExtendedInfo) {
            return $result;
        }

        $response['data'] = $result;

        return $response;
    }


    /**
     * @param array $requests
     * @return array
     * @throws Service\Exception
     */
    public function fetchMany(array $requests)
    {
        $result = [];
        $timings = [];
        if (!function_exists('curl_multi_init')) {
            foreach ($requests as $request) {
                $action = $request['action'];
                $requestName = $request['requestName'] ?? $action;
                $start = microtime(true);
                $result[$requestName] = $this->fetch(
                    $request['action'],
                    $request['params']
                );
                $timings[$requestName] = microtime(true) - $start;
            }
            return $result;
        }

        $postData = null;
        if ($this->accessToken) {
            $postData = ['access_token' => $this->accessToken];
        }

        list($multiHandle, $curlHandles) = $this->buildMultiHandles($requests, $postData);

        $running = null;
        try {
            $this->execMultiHandle($multiHandle, $running);

            foreach ($curlHandles as $requestName => $curlHandle) {
                curl_multi_remove_handle($multiHandle, $curlHandle);

                $result[$requestName] = $this->parseMultiResponse($curlHandle);
                $timings[$requestName] = curl_getinfo($curlHandle, CURLINFO_TOTAL_TIME);

                curl_close($curlHandle);
                unset($curlHandles[$requestName]);
            }
        } finally { // If an exception is thrown still need to clean up handles to avoid leak
            foreach ($curlHandles as $curlHandle) {
                curl_multi_remove_handle($multiHandle, $curlHandle);
                curl_close($curlHandle);
            }
            curl_multi_close($multiHandle);
        }

        print_r($timings);

        return $result;
    }

    /**
     * @param array $requests
     * @param array|null $postData
     * @return array
     */
    private function buildMultiHandles(array $requests, ?array $postData)
    {
        $curlHandles = [];
        $multiHandle = curl_multi_init();
        $allowedProtocols = Config::getInstance()->General['allowed_outgoing_protocols'];

        foreach ($requests as $request) {
            $requestName = $request['requestName'];
            $action = $request['action'];
            $params = $request['params'];

            if ($action && $requestName && is_array($params)) {
                $endpoint = sprintf('%s/api/%s/', $this->domain, $this->version);
                $query = Http::buildQuery($params);
                $url   = sprintf('%s%s?%s', $endpoint, $action, $query);

                $curlHandle = curl_init($url);
                $curlHandles[$requestName] = $curlHandle;

                curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, static::HTTP_REQUEST_TIMEOUT);
                curl_setopt($curlHandle, CURLOPT_TIMEOUT, static::HTTP_REQUEST_TIMEOUT);

                curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'POST');
                if ($postData) {
                    curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $postData);
                }

                if ((string)ini_get('safe_mode') == '' && ini_get('open_basedir') == '') {
                    $protocols = 0;
                    foreach (explode(',', $allowedProtocols) as $protocol) {
                        if (defined('CURLPROTO_' . strtoupper(trim($protocol)))) {
                            $protocols |= constant('CURLPROTO_' . strtoupper(trim($protocol)));
                        }
                    }

                    @curl_setopt_array($curlHandle, [
                        CURLOPT_FOLLOWLOCATION  => true,
                        CURLOPT_REDIR_PROTOCOLS => $protocols,
                        CURLOPT_MAXREDIRS       => 5,
                    ]);
                }

                curl_multi_add_handle($multiHandle, $curlHandle);
            }
        }

        return [$multiHandle, $curlHandles];
    }

    /**
     * @param resource $multiHandle
     * @param int $running
     * @return void
     */
    private function execMultiHandle($multiHandle, &$running)
    {
        do {
            curl_multi_exec($multiHandle, $running);
            if ($running) {
                curl_multi_select($multiHandle, 1.0);
            }
        } while ($running);
    }

    /**
     * @param resource $curlHandle
     * @return mixed
     * @throws Exception
     */
    private function parseMultiResponse($curlHandle)
    {
        $response = curl_multi_getcontent($curlHandle);
        $errno = curl_errno($curlHandle);
        $httpStatus = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        return $this->parseMultiPayload($response, $errno, $httpStatus);
    }

    /**
     * @param mixed $response
     * @param int $errno
     * @param int $httpStatus
     * @return mixed
     * @throws Exception
     */
    private function parseMultiPayload($response, int $errno, int $httpStatus)
    {
        if ($errno !== 0 || $response === false) {
            throw new Service\Exception(
                'There was an error reading the response from the Marketplace. Please try again later.',
                Service\Exception::HTTP_ERROR
            );
        }

        if (null === $response) {
            throw new Service\Exception(
                'There was an error reading the response from the Marketplace. Please try again later.',
                Service\Exception::HTTP_ERROR
            );
        }

        $decodedResponse = $response;
        if ('' !== $response) {
            $decodedResponse = json_decode($response, true);

            if (null === $decodedResponse) {
                throw new Service\Exception(
                    'There was an error reading the response from the Marketplace. Please try again later.',
                    Service\Exception::HTTP_ERROR
                );
            }

            if (!empty($decodedResponse['error'])) {
                throw new Service\Exception($decodedResponse['error'], Service\Exception::API_ERROR);
            }
        }

        if ($httpStatus < 200 || $httpStatus >= 400) {
            throw new Service\Exception(
                'There was an error reading the response from the Marketplace. Please try again later.',
                Service\Exception::HTTP_ERROR
            );
        }

        return $decodedResponse;
    }


    /**
     * Get the domain that is used in order to access the Marketplace. Eg https://plugins.matomo.org
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }
}

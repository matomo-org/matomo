<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\Api;

use Piwik\Http;

class Service
{
    public const CACHE_TIMEOUT_IN_SECONDS = 1200;
    public const HTTP_REQUEST_TIMEOUT = 60;

    /**
     * Actions the Marketplace answers the same for every consumer, so they are sent without the access token and
     * stay a cacheable GET.
     */
    private const ACTIONS_WITHOUT_ACCESS_TOKEN = ['info'];

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
     */
    private string $version = '2.0';

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
     * Downloads data from the given URL. If a destination path is given, the downloaded data will be stored in the
     * given path and returned otherwise.
     *
     * POST is requested whenever post data is passed or {@link authenticate()} has added the access token, so neither
     * ends up in the URL. Otherwise it is a GET.
     *
     * Make sure to call {@link authenticate()} to download paid plugins.
     *
     * @param string $url An absolute URL to the marketplace including domain.
     * @param null|string $destinationPath
     * @param null|int $timeout Defaults to 60 seconds see {@link self::HTTP_REQUEST_TIMEOUT}
     * @param null|array $postData eg array('email' => 'user@example.org')
     * @param bool $getExtendedInfo Return the extended response info for the HTTP request.
     * @param bool $withAccessToken Pass false to leave the access token off a request the Marketplace does not
     *                              personalise.
     * @return bool|string Returns the downloaded data or true if a destination path was given.
     * @throws \Exception
     */
    public function download(
        $url,
        $destinationPath = null,
        $timeout = null,
        ?array $postData = null,
        bool $getExtendedInfo = false,
        bool $withAccessToken = true
    ) {
        $method = Http::getTransportMethod();

        if (!isset($timeout)) {
            $timeout = static::HTTP_REQUEST_TIMEOUT;
        }

        if ($withAccessToken && $this->accessToken) {
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
            $httpMethod = null === $postData ? 'GET' : 'POST',
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

        $withAccessToken = !in_array($action, self::ACTIONS_WITHOUT_ACCESS_TOKEN, true);
        $response = $this->download($url, null, null, $postData, true, $withAccessToken);
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
     * Get the domain that is used in order to access the Marketplace. Eg https://plugins.matomo.org
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }
}

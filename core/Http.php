<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Composer\CaBundle\CaBundle;
use Exception;
use Piwik\Container\StaticContainer;

/**
 * Contains HTTP client related helper methods that can retrieve content from remote servers
 * and optionally save to a local file.
 *
 * Used to check for the latest Piwik version and download updates.
 *
 */
class Http
{
    /**
     * Returns the "best" available transport method for {@link sendHttpRequest()} calls.
     *
     * @return string|null Either curl, fopen, socket or null if no method is supported.
     * @api
     */
    public static function getTransportMethod()
    {
        $method = 'curl';
        if (!self::isCurlEnabled()) {
            $method = 'fopen';
            if (@ini_get('allow_url_fopen') != '1') {
                $method = 'socket';
                if (!self::isSocketEnabled()) {
                    return null;
                }
            }
        }
        return $method;
    }

    protected static function isSocketEnabled()
    {
        return function_exists('fsockopen');
    }

    protected static function isCurlEnabled()
    {
        return function_exists('curl_init') && function_exists('curl_exec');
    }

    /**
     * Sends an HTTP request using best available transport method.
     *
     * @param string $aUrl The target URL.
     * @param int $timeout The number of seconds to wait before aborting the HTTP request.
     * @param string|null $userAgent The user agent to use.
     * @param string|null $destinationPath If supplied, the HTTP response will be saved to the file specified by
     *                                     this path.
     * @param int|null $followDepth Internal redirect count. Should always pass `null` for this parameter.
     * @param bool $acceptLanguage The value to use for the `'Accept-Language'` HTTP request header.
     * @param array|bool $byteRange For `Range:` header. Should be two element array of bytes, eg, `array(0, 1024)`
     *                              Doesn't work w/ `fopen` transport method.
     * @param bool $getExtendedInfo If true returns the status code, headers & response, if false just the response.
     * @param string $httpMethod The HTTP method to use. Defaults to `'GET'`.
     * @param string $httpUsername HTTP Auth username
     * @param string $httpPassword HTTP Auth password
     * @param bool $checkHostIsAllowed whether we should check if the target host is allowed or not. This should only
     *                                 be set to false when using a hardcoded URL.
     *
     * @throws Exception if the response cannot be saved to `$destinationPath`, if the HTTP response cannot be sent,
     *                   if there are more than 5 redirects or if the request times out.
     * @return bool|string If `$destinationPath` is not specified the HTTP response is returned on success. `false`
     *                     is returned on failure.
     *                     If `$getExtendedInfo` is `true` and `$destinationPath` is not specified an array with
     *                     the following information is returned on success:
     *
     *                     - **status**: the HTTP status code
     *                     - **headers**: the HTTP headers
     *                     - **data**: the HTTP response data
     *
     *                     `false` is still returned on failure.
     * @api
     */
    public static function sendHttpRequest($aUrl,
                                           $timeout,
                                           $userAgent = null,
                                           $destinationPath = null,
                                           $followDepth = 0,
                                           $acceptLanguage = false,
                                           $byteRange = false,
                                           $getExtendedInfo = false,
                                           $httpMethod = 'GET',
                                           $httpUsername = null,
                                           $httpPassword = null,
                                           $checkHostIsAllowed = true)
    {
        // create output file
        $file = self::ensureDestinationDirectoryExists($destinationPath);

        $acceptLanguage = $acceptLanguage ? 'Accept-Language: ' . $acceptLanguage : '';
        return self::sendHttpRequestBy(self::getTransportMethod(), $aUrl, $timeout, $userAgent, $destinationPath, $file,
            $followDepth, $acceptLanguage, $acceptInvalidSslCertificate = false, $byteRange, $getExtendedInfo, $httpMethod,
            $httpUsername, $httpPassword, null, [], null, $checkHostIsAllowed);
    }

    public static function ensureDestinationDirectoryExists($destinationPath)
    {
        if ($destinationPath) {
            Filesystem::mkdir(dirname($destinationPath));
            if (($file = @fopen($destinationPath, 'wb')) === false || !is_resource($file)) {
                throw new Exception('Error while creating the file: ' . $destinationPath);
            }

            return $file;
        }

        return null;
    }

    private static function convertWildcardToPattern($wildcardHost)
    {
        $flexibleStart = $flexibleEnd = false;
        if (strpos($wildcardHost, '*.') === 0) {
            $flexibleStart = true;
            $wildcardHost = substr($wildcardHost, 2);
        }
        if (Common::stringEndsWith($wildcardHost, '.*')) {
            $flexibleEnd = true;
            $wildcardHost = substr($wildcardHost, 0, -2);
        }
        $pattern = preg_quote($wildcardHost);

        if ($flexibleStart) {
            $pattern = '.*\.' . $pattern;
        }

        if ($flexibleEnd) {
            $pattern .= '\..*';
        }

        return '/^' . $pattern . '$/i';
    }

    /**
     * Sends an HTTP request using the specified transport method.
     *
     * @param string $method
     * @param string $aUrl
     * @param int $timeout in seconds
     * @param string $userAgent
     * @param string $destinationPath
     * @param resource $file
     * @param int $followDepth
     * @param bool|string $acceptLanguage Accept-language header
     * @param bool $acceptInvalidSslCertificate Only used with $method == 'curl'. If set to true (NOT recommended!) the SSL certificate will not be checked
     * @param array|bool $byteRange For Range: header. Should be two element array of bytes, eg, array(0, 1024)
     *                                                  Doesn't work w/ fopen method.
     * @param bool $getExtendedInfo True to return status code, headers & response, false if just response.
     * @param string $httpMethod The HTTP method to use. Defaults to `'GET'`.
     * @param string $httpUsername HTTP Auth username
     * @param string $httpPassword HTTP Auth password
     * @param array|string $requestBody If $httpMethod is 'POST' this may accept an array of variables or a string that needs to be posted
     * @param array $additionalHeaders List of additional headers to set for the request
     * @param bool $checkHostIsAllowed whether we should check if the target host is allowed or not. This should only
     *                                 be set to false when using a hardcoded URL.
     *
     * @return string|array  true (or string/array) on success; false on HTTP response error code (1xx or 4xx)
     *@throws Exception
     */
    public static function sendHttpRequestBy(
        $method,
        $aUrl,
        $timeout,
        $userAgent = null,
        $destinationPath = null,
        $file = null,
        $followDepth = 0,
        $acceptLanguage = false,
        $acceptInvalidSslCertificate = false,
        $byteRange = false,
        $getExtendedInfo = false,
        $httpMethod = 'GET',
        $httpUsername = null,
        $httpPassword = null,
        $requestBody = null,
        $additionalHeaders = array(),
        $forcePost = null,
        $checkHostIsAllowed = true
    ) {
        if ($followDepth > 5) {
            throw new Exception('Too many redirects (' . $followDepth . ')');
        }


        $aUrl = preg_replace('/[\x00-\x1F\x7F]/', '', trim($aUrl));
        $parsedUrl = @parse_url($aUrl);

        if (empty($parsedUrl['scheme'])) {
            throw new Exception('Missing scheme in given url');
        }

        $allowedProtocols = Config::getInstance()->General['allowed_outgoing_protocols'];
        $isAllowed = false;

        foreach (explode(',', $allowedProtocols) as $protocol) {
            if (strtolower($parsedUrl['scheme']) === strtolower(trim($protocol))) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            throw new Exception(sprintf(
                'Protocol %s not in list of allowed protocols: %s',
                $parsedUrl['scheme'],
                $allowedProtocols
            ));
        }

        if ($checkHostIsAllowed) {
            $disallowedHosts = StaticContainer::get('http.blocklist.hosts');

            $isBlocked = false;

            foreach ($disallowedHosts as $host) {
                if (!empty($parsedUrl['host']) && preg_match(self::convertWildcardToPattern($host), $parsedUrl['host']) === 1) {
                    $isBlocked = true;
                    break;
                }
            }

            if ($isBlocked) {
                throw new Exception(sprintf(
                    'Hostname %s is in list of disallowed hosts',
                    $parsedUrl['host']
                ));
            }
        }

        $contentLength = 0;
        $fileLength = 0;

        if ( !empty($requestBody ) && is_array($requestBody )) {
            $requestBodyQuery = self::buildQuery($requestBody );
        } else {
	        $requestBodyQuery = $requestBody;
        }

        // Piwik services behave like a proxy, so we should act like one.
        $xff = 'X-Forwarded-For: '
            . (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] . ',' : '')
            . IP::getIpFromHeader();

        if (empty($userAgent)) {
            $userAgent = self::getUserAgent();
        }

        $via = 'Via: '
            . (isset($_SERVER['HTTP_VIA']) && !empty($_SERVER['HTTP_VIA']) ? $_SERVER['HTTP_VIA'] . ', ' : '')
            . Version::VERSION . ' '
            . ($userAgent ? " ($userAgent)" : '');

        // range header
        $rangeBytes = '';
        $rangeHeader = '';
        if (!empty($byteRange)) {
            $rangeBytes = $byteRange[0] . '-' . $byteRange[1];
            $rangeHeader = 'Range: bytes=' . $rangeBytes . "\r\n";
        }

        [$proxyHost, $proxyPort, $proxyUser, $proxyPassword] = self::getProxyConfiguration($aUrl);

        // other result data
        $status  = null;
        $headers = array();
        $response = null;

        $httpAuthIsUsed = !empty($httpUsername) || !empty($httpPassword);

	    $httpAuth = '';
	    if ($httpAuthIsUsed) {
		    $httpAuth = 'Authorization: Basic ' . base64_encode($httpUsername.':'.$httpPassword) . "\r\n";
	    }

	    $httpEventParams = array(
		    'httpMethod' => $httpMethod,
		    'body' => $requestBody,
		    'userAgent' => $userAgent,
		    'timeout' => $timeout,
		    'headers' => array_map('trim', array_filter(array_merge(array(
			    $rangeHeader, $via, $xff, $httpAuth, $acceptLanguage
		    ), $additionalHeaders))),
		    'verifySsl' => !$acceptInvalidSslCertificate,
		    'destinationPath' => $destinationPath
	    );

	    /**
	     * Triggered to send an HTTP request. Allows plugins to resolve the HTTP request themselves or to find out
	     * when an HTTP request is triggered to log this information for example to a monitoring tool.
	     *
	     * @param string $url The URL that needs to be requested
	     * @param array $params HTTP params like
	     *                      - 'httpMethod' (eg GET, POST, ...),
	     *                      - 'body' the request body if the HTTP method needs to be posted
	     *                      - 'userAgent'
	     *                      - 'timeout' After how many seconds a request should time out
	     *                      - 'headers' An array of header strings like array('Accept-Language: en', '...')
	     *                      - 'verifySsl' A boolean whether SSL certificate should be verified
	     *                      - 'destinationPath' If set, the response of the HTTP request should be saved to this file
	     * @param string &$response A plugin listening to this event should assign the HTTP response it received to this variable, for example "{value: true}"
	     * @param string &$status A plugin listening to this event should assign the HTTP status code it received to this variable, for example "200"
	     * @param array &$headers A plugin listening to this event should assign the HTTP headers it received to this variable, eg array('Content-Length' => '5')
	     */
        Piwik::postEvent('Http.sendHttpRequest', array($aUrl, $httpEventParams, &$response, &$status, &$headers));

	    if ($response !== null || $status !== null || !empty($headers)) {
	    	// was handled by event above...
		    /**
		     * described below
		     * @ignore
		     */
                    Piwik::postEvent('Http.sendHttpRequest.end', array($aUrl, $httpEventParams, &$response, &$status, &$headers));

                    if ($destinationPath && file_exists($destinationPath)) {
                        return true;
                    }
                    if ($getExtendedInfo) {
                        return array(
                            'status'  => $status,
                            'headers' => $headers,
                            'data'    => $response
                        );
		    } else {
                        return trim($response);
		    }
	    }

        if ($method == 'socket') {
            if (!self::isSocketEnabled()) {
                // can be triggered in tests
                throw new Exception("HTTP socket support is not enabled (php function fsockopen is not available) ");
            }
            // initialization
            $url = @parse_url($aUrl);
            if ($url === false || !isset($url['scheme'])) {
                throw new Exception('Malformed URL: ' . $aUrl);
            }

            if ($url['scheme'] != 'http' && $url['scheme'] != 'https') {
                throw new Exception('Invalid protocol/scheme: ' . $url['scheme']);
            }
            $host = $url['host'];
            $port = isset($url['port']) ? $url['port'] : ('https' == $url['scheme'] ? 443 : 80);
            $path = isset($url['path']) ? $url['path'] : '/';
            if (isset($url['query'])) {
                $path .= '?' . $url['query'];
            }
            $errno = null;
            $errstr = null;

            if ((!empty($proxyHost) && !empty($proxyPort))
                || !empty($byteRange)
            ) {
                $httpVer = '1.1';
            } else {
                $httpVer = '1.0';
            }

            $proxyAuth = null;
            if (!empty($proxyHost) && !empty($proxyPort)) {
                $connectHost = $proxyHost;
                $connectPort = $proxyPort;
                if (!empty($proxyUser) && !empty($proxyPassword)) {
                    $proxyAuth = 'Proxy-Authorization: Basic ' . base64_encode("$proxyUser:$proxyPassword") . "\r\n";
                }
                $requestHeader = "$httpMethod $aUrl HTTP/$httpVer\r\n";
            } else {
                $connectHost = $host;
                $connectPort = $port;
                $requestHeader = "$httpMethod $path HTTP/$httpVer\r\n";

                if ('https' == $url['scheme']) {
                    $connectHost = 'ssl://' . $connectHost;
                }
            }

            // connection attempt
            if (($fsock = @fsockopen($connectHost, $connectPort, $errno, $errstr, $timeout)) === false || !is_resource($fsock)) {
                if (is_resource($file)) {
                    @fclose($file);
                }
                throw new Exception("Error while connecting to: $host. Please try again later. $errstr");
            }

            // send HTTP request header
            $requestHeader .=
                "Host: $host" . ($port != 80 && ('https' == $url['scheme'] && $port != 443) ? ':' . $port : '') . "\r\n"
                . ($httpAuth ? $httpAuth : '')
                . ($proxyAuth ? $proxyAuth : '')
                . 'User-Agent: ' . $userAgent . "\r\n"
                . ($acceptLanguage ? $acceptLanguage . "\r\n" : '')
                . $xff . "\r\n"
                . $via . "\r\n"
                . $rangeHeader
                . (!empty($additionalHeaders) ? implode("\r\n", $additionalHeaders) . "\r\n" : '')
                . "Connection: close\r\n";
            fwrite($fsock, $requestHeader);

            if (strtolower($httpMethod) === 'post' && !empty($requestBodyQuery )) {
                fwrite($fsock, self::buildHeadersForPost($requestBodyQuery ));
                fwrite($fsock, "\r\n");
                fwrite($fsock, $requestBodyQuery );
            } else {
                fwrite($fsock, "\r\n");
            }

            $streamMetaData = array('timed_out' => false);
            @stream_set_blocking($fsock, true);

            if (function_exists('stream_set_timeout')) {
                @stream_set_timeout($fsock, $timeout);
            } elseif (function_exists('socket_set_timeout')) {
                @socket_set_timeout($fsock, $timeout);
            }

            // process header
            $status = null;

            while (!feof($fsock)) {
                $line = fgets($fsock, 4096);

                $streamMetaData = @stream_get_meta_data($fsock);
                if ($streamMetaData['timed_out']) {
                    if (is_resource($file)) {
                        @fclose($file);
                    }
                    @fclose($fsock);
                    throw new Exception('Timed out waiting for server response');
                }

                // a blank line marks the end of the server response header
                if (rtrim($line, "\r\n") == '') {
                    break;
                }

                // parse first line of server response header
                if (!$status) {
                    // expect first line to be HTTP response status line, e.g., HTTP/1.1 200 OK
                    if (!preg_match('~^HTTP/(\d\.\d)\s+(\d+)(\s*.*)?~', $line, $m)) {
                        if (is_resource($file)) {
                            @fclose($file);
                        }
                        @fclose($fsock);
                        throw new Exception('Expected server response code.  Got ' . rtrim($line, "\r\n"));
                    }

                    $status = (integer)$m[2];

                    // Informational 1xx or Client Error 4xx
                    if ($status < 200 || $status >= 400) {
                        if (is_resource($file)) {
                            @fclose($file);
                        }
                        @fclose($fsock);

                        if (!$getExtendedInfo) {
                            return false;
                        } else {
                            return array('status' => $status);
                        }
                    }

                    continue;
                }

                // handle redirect
                if (preg_match('/^Location:\s*(.+)/', rtrim($line, "\r\n"), $m)) {
                    if (is_resource($file)) {
                        @fclose($file);
                    }
                    @fclose($fsock);
                    // Successful 2xx vs Redirect 3xx
                    if ($status < 300) {
                        throw new Exception('Unexpected redirect to Location: ' . rtrim($line) . ' for status code ' . $status);
                    }
                    return self::sendHttpRequestBy(
                        $method,
                        trim($m[1]),
                        $timeout,
                        $userAgent,
                        $destinationPath,
                        $file,
                        $followDepth + 1,
                        $acceptLanguage,
                        $acceptInvalidSslCertificate = false,
                        $byteRange,
                        $getExtendedInfo,
                        $httpMethod,
                        $httpUsername,
                        $httpPassword,
                        $requestBodyQuery,
                        $additionalHeaders
                    );
                }

                // save expected content length for later verification
                if (preg_match('/^Content-Length:\s*(\d+)/', $line, $m)) {
                    $contentLength = (integer)$m[1];
                }

                self::parseHeaderLine($headers, $line);
            }

            if (feof($fsock)
                && $httpMethod != 'HEAD'
            ) {
                throw new Exception('Unexpected end of transmission');
            }

            // process content/body
            $response = '';

            while (!feof($fsock)) {
                $line = fread($fsock, 8192);

                $streamMetaData = @stream_get_meta_data($fsock);
                if ($streamMetaData['timed_out']) {
                    if (is_resource($file)) {
                        @fclose($file);
                    }
                    @fclose($fsock);
                    throw new Exception('Timed out waiting for server response');
                }

                $fileLength += strlen($line);

                if (is_resource($file)) {
                    // save to file
                    fwrite($file, $line);
                } else {
                    // concatenate to response string
                    $response .= $line;
                }
            }

            // determine success or failure
            @fclose(@$fsock);
        } elseif ($method == 'fopen') {
            $response = false;

            // we make sure the request takes less than a few seconds to fail
            // we create a stream_context (works in php >= 5.2.1)
            // we also set the socket_timeout (for php < 5.2.1)
            $default_socket_timeout = @ini_get('default_socket_timeout');
            @ini_set('default_socket_timeout', $timeout);

            $ctx = null;
            if (function_exists('stream_context_create')) {
                $stream_options = array(
                    'http' => array(
                        'header'        => 'User-Agent: ' . $userAgent . "\r\n"
                            . ($httpAuth ? $httpAuth : '')
                            . ($acceptLanguage ? $acceptLanguage . "\r\n" : '')
                            . $xff . "\r\n"
                            . $via . "\r\n"
                            . (!empty($additionalHeaders) ? implode("\r\n", $additionalHeaders) . "\r\n" : '')
                            . $rangeHeader,
                        'max_redirects' => 5, // PHP 5.1.0
                        'timeout'       => $timeout, // PHP 5.2.1
                    )
                );

                if (!empty($proxyHost) && !empty($proxyPort)) {
                    $stream_options['http']['proxy'] = 'tcp://' . $proxyHost . ':' . $proxyPort;
                    $stream_options['http']['request_fulluri'] = true; // required by squid proxy
                    if (!empty($proxyUser) && !empty($proxyPassword)) {
                        $stream_options['http']['header'] .= 'Proxy-Authorization: Basic ' . base64_encode("$proxyUser:$proxyPassword") . "\r\n";
                    }
                }

                if (strtolower($httpMethod) === 'post' && !empty($requestBodyQuery )) {
                    $postHeader  = self::buildHeadersForPost($requestBodyQuery );
                    $postHeader .= "\r\n";
                    $stream_options['http']['method']  = 'POST';
                    $stream_options['http']['header'] .= $postHeader;
                    $stream_options['http']['content'] = $requestBodyQuery;
                }

                $ctx = stream_context_create($stream_options);
            }

            // save to file
            if (is_resource($file)) {
                if (!($handle = fopen($aUrl, 'rb', false, $ctx))) {
                    throw new Exception("Unable to open $aUrl");
                }
                while (!feof($handle)) {
                    $response = fread($handle, 8192);
                    $fileLength += strlen($response);
                    fwrite($file, $response);
                }
                fclose($handle);
            } else {
                $response = @file_get_contents($aUrl, 0, $ctx);

                // try to get http status code from response headers
                if (isset($http_response_header) && preg_match('~^HTTP/(\d\.\d)\s+(\d+)(\s*.*)?~', implode("\n", $http_response_header), $m)) {
                    $status = (int)$m[2];
                }

                if (!$status && $response === false) {
                    $error = ErrorHandler::getLastError();
                    throw new \Exception($error);
                }
                $fileLength = strlen($response);
            }

            foreach ($http_response_header as $line) {
                self::parseHeaderLine($headers, $line);
            }

            // restore the socket_timeout value
            if (!empty($default_socket_timeout)) {
                @ini_set('default_socket_timeout', $default_socket_timeout);
            }
        } elseif ($method == 'curl') {
            if (!self::isCurlEnabled()) {
                // can be triggered in tests
                throw new Exception("CURL is not enabled in php.ini, but is being used.");
            }
            $ch = @curl_init();

            if (!empty($proxyHost) && !empty($proxyPort)) {
                @curl_setopt($ch, CURLOPT_PROXY, $proxyHost . ':' . $proxyPort);
                if (!empty($proxyUser) && !empty($proxyPassword)) {
                    // PROXYAUTH defaults to BASIC
                    @curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyUser . ':' . $proxyPassword);
                }
            }

            $curl_options = array(
                // internal to ext/curl
                CURLOPT_BINARYTRANSFER => is_resource($file),

                // curl options (sorted oldest to newest)
                CURLOPT_URL            => $aUrl,
                CURLOPT_USERAGENT      => $userAgent,
                CURLOPT_HTTPHEADER     => array_merge(array(
                    $xff,
                    $via,
                    $acceptLanguage
                ), $additionalHeaders),
                // only get header info if not saving directly to file
                CURLOPT_HEADER         => is_resource($file) ? false : true,
                CURLOPT_CONNECTTIMEOUT => $timeout,
                CURLOPT_TIMEOUT        => $timeout,
            );

            if ($rangeBytes) {
                curl_setopt($ch, CURLOPT_RANGE, $rangeBytes);
            } else {
                // see https://github.com/matomo-org/matomo/pull/17009 for more info
                // NOTE: we only do this when CURLOPT_RANGE is not being used, because when using both the
                // response is empty.
                $curl_options[CURLOPT_ENCODING] = "";
            }

            // Case core:archive command is triggering archiving on https:// and the certificate is not valid
            if ($acceptInvalidSslCertificate) {
                $curl_options += array(
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                );
            }
            @curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpMethod);
            if ($httpMethod == 'HEAD') {
                @curl_setopt($ch, CURLOPT_NOBODY, true);
            }

            if (strtolower($httpMethod) === 'post' && !empty($requestBodyQuery )) {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBodyQuery );
            }

            if (!empty($httpUsername) && !empty($httpPassword)) {
                $curl_options += array(
                    CURLOPT_USERPWD => $httpUsername . ':' . $httpPassword,
                );
            }

            @curl_setopt_array($ch, $curl_options);
            self::configCurlCertificate($ch);

            /*
             * as of php 5.2.0, CURLOPT_FOLLOWLOCATION can't be set if
             * in safe_mode or open_basedir is set
             */
            if ((string)ini_get('safe_mode') == '' && ini_get('open_basedir') == '') {
                $protocols = 0;

                foreach (explode(',', $allowedProtocols) as $protocol) {
                    if (defined('CURLPROTO_' . strtoupper(trim($protocol)))) {
                        $protocols |= constant('CURLPROTO_' . strtoupper(trim($protocol)));
                    }
                }

                $curl_options = array(
                    // curl options (sorted oldest to newest)
                    CURLOPT_FOLLOWLOCATION  => true,
                    CURLOPT_REDIR_PROTOCOLS => $protocols,
                    CURLOPT_MAXREDIRS       => 5,
                );
                if ($forcePost) {
                    $curl_options[CURLOPT_POSTREDIR] = CURL_REDIR_POST_ALL;
                }
                @curl_setopt_array($ch, $curl_options);
            }

            if (is_resource($file)) {
                // write output directly to file
                @curl_setopt($ch, CURLOPT_FILE, $file);
            } else {
                // internal to ext/curl
                @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            }

            ob_start();
            $response = @curl_exec($ch);
            ob_end_clean();

            if ($response === true) {
                $response = '';
            } elseif ($response === false) {
                $errstr = curl_error($ch);
                if ($errstr != '') {
                    throw new Exception('curl_exec: ' . $errstr
                        . '. Hostname requested was: ' . UrlHelper::getHostFromUrl($aUrl));
                }
                $response = '';
            } else {
                $header = '';
                // redirects are included in the output html, so we look for the last line that starts w/ HTTP/...
                // to split the response
                while (substr($response, 0, 5) == "HTTP/") {
                    $split = explode("\r\n\r\n", $response, 2);

                    if(count($split) == 2) {
                        [$header, $response] = $split;
                    } else {
                        $response = '';
                        $header = reset($split);
                    }
                }

                foreach (explode("\r\n", $header) as $line) {
                    self::parseHeaderLine($headers, $line);
                }
            }

            $contentLength = @curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
            $fileLength = is_resource($file) ? @curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD) : strlen($response);
            $status = @curl_getinfo($ch, CURLINFO_HTTP_CODE);

            @curl_close($ch);
            unset($ch);
        } else {
            throw new Exception('Invalid request method: ' . $method);
        }

        if (is_resource($file)) {
            fflush($file);
            @fclose($file);

            $fileSize = filesize($destinationPath);
            if ($contentLength > 0
                && $fileSize != $contentLength
            ) {
                throw new Exception('File size error: ' . $destinationPath . '; expected ' . $contentLength . ' bytes; received ' . $fileLength . ' bytes; saved ' . $fileSize . ' bytes to file');
            }
            return true;
        }

	    /**
	     * Triggered when an HTTP request finished. A plugin can for example listen to this and alter the response,
	     * status code, or finish a timer in case the plugin is measuring how long it took to execute the request
	     *
	     * @param string $url The URL that needs to be requested
	     * @param array $params HTTP params like
	     *                      - 'httpMethod' (eg GET, POST, ...),
	     *                      - 'body' the request body if the HTTP method needs to be posted
	     *                      - 'userAgent'
	     *                      - 'timeout' After how many seconds a request should time out
	     *                      - 'headers' An array of header strings like array('Accept-Language: en', '...')
	     *                      - 'verifySsl' A boolean whether SSL certificate should be verified
	     *                      - 'destinationPath' If set, the response of the HTTP request should be saved to this file
	     * @param string &$response The response of the HTTP request, for example "{value: true}"
	     * @param string &$status The returned HTTP status code, for example "200"
	     * @param array &$headers The returned headers, eg array('Content-Length' => '5')
	     */
	    Piwik::postEvent('Http.sendHttpRequest.end', array($aUrl, $httpEventParams, &$response, &$status, &$headers));

        if (!$getExtendedInfo) {
            return trim($response);
        } else {
            return array(
                'status'  => $status,
                'headers' => $headers,
                'data'    => $response
            );
        }
    }

    public static function buildQuery($params)
    {
        return http_build_query($params, '', '&');
    }

    private static function buildHeadersForPost($requestBody)
    {
        $postHeader  = "Content-Type: application/x-www-form-urlencoded\r\n";
        $postHeader .= "Content-Length: " . strlen($requestBody) . "\r\n";

        return $postHeader;
    }

    /**
     * Downloads the next chunk of a specific file. The next chunk's byte range
     * is determined by the existing file's size and the expected file size, which
     * is stored in the option table before starting a download. The expected
     * file size is obtained through a `HEAD` HTTP request.
     *
     * _Note: this function uses the **Range** HTTP header to accomplish downloading in
     * parts. Not every server supports this header._
     *
     * The proper use of this function is to call it once per request. The browser
     * should continue to send requests to Piwik which will in turn call this method
     * until the file has completely downloaded. In this way, the user can be informed
     * of a download's progress.
     *
     * **Example Usage**
     *
     * ```
     * // browser JavaScript
     * var downloadFile = function (isStart) {
     *     var ajax = new ajaxHelper();
     *     ajax.addParams({
     *         module: 'MyPlugin',
     *         action: 'myAction',
     *         isStart: isStart ? 1 : 0
     *     }, 'post');
     *     ajax.setCallback(function (response) {
     *         var progress = response.progress
     *         // ...update progress...
     *
     *         downloadFile(false);
     *     });
     *     ajax.send();
     * }
     *
     * downloadFile(true);
     * ```
     *
     * ```
     * // PHP controller action
     * public function myAction()
     * {
     *     $outputPath = PIWIK_INCLUDE_PATH . '/tmp/averybigfile.zip';
     *     $isStart = Common::getRequestVar('isStart', 1, 'int');
     *     Http::downloadChunk("http://bigfiles.com/averybigfile.zip", $outputPath, $isStart == 1);
     * }
     * ```
     *
     * @param string $url The url to download from.
     * @param string $outputPath The path to the file to save/append to.
     * @param bool $isContinuation `true` if this is the continuation of a download,
     *                             or if we're starting a fresh one.
     * @throws Exception if the file already exists and we're starting a new download,
     *                   if we're trying to continue a download that never started
     * @return array
     * @api
     */
    public static function downloadChunk($url, $outputPath, $isContinuation)
    {
        // make sure file doesn't already exist if we're starting a new download
        if (!$isContinuation
            && file_exists($outputPath)
        ) {
            throw new Exception(
                Piwik::translate('General_DownloadFail_FileExists', "'" . $outputPath . "'")
                . ' ' . Piwik::translate('General_DownloadPleaseRemoveExisting'));
        }

        // if we're starting a download, get the expected file size & save as an option
        $downloadOption = $outputPath . '_expectedDownloadSize';
        if (!$isContinuation) {
            $expectedFileSizeResult = Http::sendHttpRequest(
                $url,
                $timeout = 300,
                $userAgent = null,
                $destinationPath = null,
                $followDepth = 0,
                $acceptLanguage = false,
                $byteRange = false,
                $getExtendedInfo = true,
                $httpMethod = 'HEAD'
            );

            $expectedFileSize = 0;
            if (isset($expectedFileSizeResult['headers']['Content-Length'])) {
                $expectedFileSize = (int)$expectedFileSizeResult['headers']['Content-Length'];
            }

            if ($expectedFileSize == 0) {
                Log::info("HEAD request for '%s' failed, got following: %s", $url, print_r($expectedFileSizeResult, true));
                throw new Exception(Piwik::translate('General_DownloadFail_HttpRequestFail'));
            }

            Option::set($downloadOption, $expectedFileSize);
        } else {
            $expectedFileSize = (int)Option::get($downloadOption);
            if ($expectedFileSize === false) { // sanity check
                throw new Exception("Trying to continue a download that never started?! That's not supposed to happen...");
            }
        }

        // if existing file is already big enough, then fail so we don't accidentally overwrite
        // existing DB
        $existingSize = file_exists($outputPath) ? filesize($outputPath) : 0;
        if ($existingSize >= $expectedFileSize) {
            throw new Exception(
                Piwik::translate('General_DownloadFail_FileExistsContinue', "'" . $outputPath . "'")
                . ' ' . Piwik::translate('General_DownloadPleaseRemoveExisting'));
        }

        // download a chunk of the file
        $result = Http::sendHttpRequest(
            $url,
            $timeout = 300,
            $userAgent = null,
            $destinationPath = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $byteRange = array($existingSize, min($existingSize + 1024 * 1024 - 1, $expectedFileSize)),
            $getExtendedInfo = true
        );

        if ($result === false
            || $result['status'] < 200
            || $result['status'] > 299
        ) {
            $result['data'] = self::truncateStr($result['data'], 1024);
            Log::info("Failed to download range '%s-%s' of file from url '%s'. Got result: %s",
                $byteRange[0], $byteRange[1], $url, print_r($result, true));

            throw new Exception(Piwik::translate('General_DownloadFail_HttpRequestFail'));
        }

        // write chunk to file
        $f = fopen($outputPath, 'ab');
        fwrite($f, $result['data']);
        fclose($f);

        clearstatcache($clear_realpath_cache = true, $outputPath);
        return array(
            'current_size'       => filesize($outputPath),
            'expected_file_size' => $expectedFileSize,
        );
    }

    /**
     * Will configure CURL handle $ch
     * to use local list of Certificate Authorities,
     */
    public static function configCurlCertificate(&$ch)
    {
        $general = Config::getInstance()->General;
        if (!empty($general['custom_cacert_pem'])) {
            $cacertPath = $general['custom_cacert_pem'];
        } else {
            $cacertPath = CaBundle::getBundledCaBundlePath();
        }
        @curl_setopt($ch, CURLOPT_CAINFO, $cacertPath);
    }

    public static function getUserAgent()
    {
        return !empty($_SERVER['HTTP_USER_AGENT'])
            ? $_SERVER['HTTP_USER_AGENT']
            : 'Matomo/' . Version::VERSION;
    }

    public static function getClientHintsFromServerVariables(): array
    {
        $clientHints = [];

        foreach ($_SERVER as $key => $value) {
            if (
                0 === strpos(strtolower($key), strtolower('HTTP_SEC_CH_UA'))
                || 'X_HTTP_REQUESTED_WITH' === strtoupper($key)
            ) {
                $clientHints[$key] = $value;
            }
        }

        ksort($clientHints);

        return $clientHints;
    }

    /**
     * Fetches a file located at `$url` and saves it to `$destinationPath`.
     *
     * @param string $url The URL of the file to download.
     * @param string $destinationPath The path to download the file to.
     * @param int $tries (deprecated)
     * @param int $timeout The amount of seconds to wait before aborting the HTTP request.
     * @throws Exception if the response cannot be saved to `$destinationPath`, if the HTTP response cannot be sent,
     *                   if there are more than 5 redirects or if the request times out.
     * @return bool `true` on success, throws Exception on failure
     * @api
     */
    public static function fetchRemoteFile($url, $destinationPath = null, $tries = 0, $timeout = 10)
    {
        @ignore_user_abort(true);
        SettingsServer::setMaxExecutionTime(0);
        return self::sendHttpRequest($url, $timeout, 'Update', $destinationPath);
    }

    /**
     * Utility function, parses an HTTP header line into key/value & sets header
     * array with them.
     *
     * @param array $headers
     * @param string $line
     */
    private static function parseHeaderLine(&$headers, $line)
    {
        $parts = explode(':', $line, 2);
        if (count($parts) == 1) {
            return;
        }

        [$name, $value] = $parts;
        $name = trim($name);
        $headers[$name] = trim($value);

        /**
         * With HTTP/2 Cloudflare is passing headers in lowercase (e.g. 'content-type' instead of 'Content-Type')
         * which breaks any code which uses the header data.
         */
        if (version_compare(PHP_VERSION, '5.5.16', '>=')) {
            // Passing a second arg to ucwords is not supported by older versions of PHP
            $camelName = ucwords($name, '-');
            if ($camelName !== $name) {
                $headers[$camelName] = trim($value);
            }
        }
    }

    /**
     * Utility function that truncates a string to an arbitrary limit.
     *
     * @param string $str The string to truncate.
     * @param int $limit The maximum length of the truncated string.
     * @return string
     */
    private static function truncateStr($str, $limit)
    {
        if (strlen($str) > $limit) {
            return substr($str, 0, $limit) . '...';
        }
        return $str;
    }

    /**
     * Returns the If-Modified-Since HTTP header if it can be found. If it cannot be
     * found, an empty string is returned.
     *
     * @return string
     */
    public static function getModifiedSinceHeader()
    {
        $modifiedSince = '';
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $modifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'];

            // strip any trailing data appended to header
            if (false !== ($semicolonPos = strpos($modifiedSince, ';'))) {
                $modifiedSince = substr($modifiedSince, 0, $semicolonPos);
            }
        }
        return $modifiedSince;
    }

    /**
     * Returns Proxy to use for connecting via HTTP to given URL
     *
     * @param string $url
     * @return array
     */
    private static function getProxyConfiguration($url)
    {
        $hostname = UrlHelper::getHostFromUrl($url);

        if (Url::isLocalHost($hostname)) {
            return array(null, null, null, null);
        }

        // proxy configuration
        $proxyHost = Config::getInstance()->proxy['host'];
        $proxyPort = Config::getInstance()->proxy['port'];
        $proxyUser = Config::getInstance()->proxy['username'];
        $proxyPassword = Config::getInstance()->proxy['password'];
        $proxyExclude = Config::getInstance()->proxy['exclude'];

        if (!empty($proxyExclude)) {
            $excludes = explode(',', $proxyExclude);
            $excludes = array_map('trim', $excludes);
            $excludes = array_filter($excludes);
            if (in_array($hostname, $excludes)) {
                return array(null, null, null, null);
            }
        }

        return array($proxyHost, $proxyPort, $proxyUser, $proxyPassword);
    }

    /**
     * Checks the request is over SSL
     * @return bool
     */
    public static function isUpdatingOverHttps()
    {
        $openSslEnabled = extension_loaded('openssl');
        $usingMethodSupportingHttps = (Http::getTransportMethod() !== 'socket');

        return $openSslEnabled && $usingMethodSupportingHttps;
    }
}

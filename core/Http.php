<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Server-side http client to retrieve content from remote servers, and optionally save to a local file.
 * Used to check for the latest Piwik version and download updates.
 *
 * @package Piwik
 */
class Piwik_Http
{
    /**
     * Get "best" available transport method for sendHttpRequest() calls.
     *
     * @return string
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
        return function_exists('curl_init');
    }

    /**
     * Sends http request ensuring the request will fail before $timeout seconds
     * If no $destinationPath is specified, the trimmed response (without header) is returned as a string.
     * If a $destinationPath is specified, the response (without header) is saved to a file.
     *
     * @param string     $aUrl
     * @param int        $timeout
     * @param string     $userAgent
     * @param string     $destinationPath
     * @param int        $followDepth
     * @param bool       $acceptLanguage
     * @param array|bool $byteRange       For Range: header. Should be two element array of bytes, eg, array(0, 1024)
     *                                    Doesn't work w/ fopen method.
     * @param bool       $getExtendedInfo True to return status code, headers & response, false if just response.
     * @param string     $httpMethod      The HTTP method to use. Defaults to 'GET'.
     *
     * @throws Exception
     * @return bool  true (or string) on success; false on HTTP response error code (1xx or 4xx)
     */
    public static function sendHttpRequest($aUrl, $timeout, $userAgent = null, $destinationPath = null, $followDepth = 0, $acceptLanguage = false, $byteRange = false, $getExtendedInfo = false, $httpMethod = 'GET')
    {
        // create output file
        $file = null;
        if ($destinationPath) {
            // Ensure destination directory exists
            Piwik_Common::mkdir(dirname($destinationPath));
            if (($file = @fopen($destinationPath, 'wb')) === false || !is_resource($file)) {
                throw new Exception('Error while creating the file: ' . $destinationPath);
            }
        }

        $acceptLanguage = $acceptLanguage ? 'Accept-Language: ' . $acceptLanguage : '';
        return self::sendHttpRequestBy(self::getTransportMethod(), $aUrl, $timeout, $userAgent, $destinationPath, $file, $followDepth, $acceptLanguage, $acceptInvalidSslCertificate = false, $byteRange, $getExtendedInfo, $httpMethod);
    }

    /**
     * Sends http request using the specified transport method
     *
     * @param string      $method
     * @param string      $aUrl
     * @param int         $timeout
     * @param string      $userAgent
     * @param string      $destinationPath
     * @param resource    $file
     * @param int         $followDepth
     * @param bool|string $acceptLanguage               Accept-language header
     * @param bool        $acceptInvalidSslCertificate  Only used with $method == 'curl'. If set to true (NOT recommended!) the SSL certificate will not be checked
     * @param array|bool  $byteRange                    For Range: header. Should be two element array of bytes, eg, array(0, 1024)
     *                                                  Doesn't work w/ fopen method.
     * @param bool        $getExtendedInfo              True to return status code, headers & response, false if just response.
     * @param string      $httpMethod                   The HTTP method to use. Defaults to 'GET'.
     *
     * @throws Exception
     * @return bool  true (or string/array) on success; false on HTTP response error code (1xx or 4xx)
     */
    public static function sendHttpRequestBy(
        $method = 'socket',
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
        $httpMethod = 'GET'
    )
    {
        if ($followDepth > 5) {
            throw new Exception('Too many redirects (' . $followDepth . ')');
        }

        $contentLength = 0;
        $fileLength = 0;

        // Piwik services behave like a proxy, so we should act like one.
        $xff = 'X-Forwarded-For: '
            . (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] . ',' : '')
            . Piwik_IP::getIpFromHeader();

        if (empty($userAgent)) {
            $userAgent = self::getUserAgent();
        }

        $via = 'Via: '
            . (isset($_SERVER['HTTP_VIA']) && !empty($_SERVER['HTTP_VIA']) ? $_SERVER['HTTP_VIA'] . ', ' : '')
            . Piwik_Version::VERSION . ' '
            . ($userAgent ? " ($userAgent)" : '');

        // range header
        $rangeHeader = '';
        if (!empty($byteRange)) {
            $rangeHeader = 'Range: bytes=' . $byteRange[0] . '-' . $byteRange[1] . "\r\n";
        }

        // proxy configuration
        $proxyHost = Piwik_Config::getInstance()->proxy['host'];
        $proxyPort = Piwik_Config::getInstance()->proxy['port'];
        $proxyUser = Piwik_Config::getInstance()->proxy['username'];
        $proxyPassword = Piwik_Config::getInstance()->proxy['password'];

        // other result data
        $status = null;
        $headers = array();

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

            if ($url['scheme'] != 'http') {
                throw new Exception('Invalid protocol/scheme: ' . $url['scheme']);
            }
            $host = $url['host'];
            $port = isset($url['port)']) ? $url['port'] : 80;
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
                "Host: $host" . ($port != 80 ? ':' . $port : '') . "\r\n"
                    . ($proxyAuth ? $proxyAuth : '')
                    . 'User-Agent: ' . $userAgent . "\r\n"
                    . ($acceptLanguage ? $acceptLanguage . "\r\n" : '')
                    . $xff . "\r\n"
                    . $via . "\r\n"
                    . $rangeHeader
                    . "Connection: close\r\n"
                    . "\r\n";
            fwrite($fsock, $requestHeader);

            $streamMetaData = array('timed_out' => false);
            @stream_set_blocking($fsock, true);

            if (function_exists('stream_set_timeout')) {
                @stream_set_timeout($fsock, $timeout);
            } elseif (function_exists('socket_set_timeout')) {
                @socket_set_timeout($fsock, $timeout);
            }

            // process header
            $status = null;
            $expectRedirect = false;

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
                        $httpMethod
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

                $fileLength += Piwik_Common::strlen($line);

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
        } else if ($method == 'fopen') {
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
                            . ($acceptLanguage ? $acceptLanguage . "\r\n" : '')
                            . $xff . "\r\n"
                            . $via . "\r\n"
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

                $ctx = stream_context_create($stream_options);
            }

            // save to file
            if (is_resource($file)) {
                $handle = fopen($aUrl, 'rb', false, $ctx);
                while (!feof($handle)) {
                    $response = fread($handle, 8192);
                    $fileLength += Piwik_Common::strlen($response);
                    fwrite($file, $response);
                }
                fclose($handle);
            } else {
                $response = file_get_contents($aUrl, 0, $ctx);
                $fileLength = Piwik_Common::strlen($response);
            }

            // restore the socket_timeout value
            if (!empty($default_socket_timeout)) {
                @ini_set('default_socket_timeout', $default_socket_timeout);
            }
        } else if ($method == 'curl') {
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
                CURLOPT_HTTPHEADER     => array(
                    $xff,
                    $via,
                    $rangeHeader,
                    $acceptLanguage
                ),
                // only get header info if not saving directly to file
                CURLOPT_HEADER         => is_resource($file) ? false : true,
                CURLOPT_CONNECTTIMEOUT => $timeout,
            );
            // Case archive.php is triggering archiving on https:// and the certificate is not valid
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

            @curl_setopt_array($ch, $curl_options);
            self::configCurlCertificate($ch);


            /*
             * as of php 5.2.0, CURLOPT_FOLLOWLOCATION can't be set if
             * in safe_mode or open_basedir is set
             */
            if ((string)ini_get('safe_mode') == '' && ini_get('open_basedir') == '') {
                $curl_options = array(
                    // curl options (sorted oldest to newest)
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS      => 5,
                );
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
            } else if ($response === false) {
                $errstr = curl_error($ch);
                if ($errstr != '') {
                    throw new Exception('curl_exec: ' . $errstr);
                }
                $response = '';
            } else {
                $header = '';
                // redirects are included in the output html, so we look for the last line that starts w/ HTTP/...
                // to split the response
                while (substr($response, 0, 5) == "HTTP/") {
                    list($header, $response) = explode("\r\n\r\n", $response, 2);
                }

                foreach (explode("\r\n", $header) as $line) {
                    self::parseHeaderLine($headers, $line);
                }
            }

            $contentLength = @curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
            $fileLength = is_resource($file) ? @curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD) : Piwik_Common::strlen($response);
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
            if ((($contentLength > 0) && ($fileLength != $contentLength))
                || ($fileSize != $fileLength)
            ) {
                throw new Exception('File size error: ' . $destinationPath . '; expected ' . $contentLength . ' bytes; received ' . $fileLength . ' bytes; saved ' . $fileSize . ' bytes to file');
            }
            return true;
        }

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

    /**
     * Downloads the next chunk of a specific file. The next chunk's byte range
     * is determined by the existing file's size and the expected file size, which
     * is stored in the piwik_option table before starting a download.
     * Note this function uses the Range HTTP header to accomplish downloading in
     * parts.
     *
     * @param string $url            The url to download from.
     * @param string $outputPath     The path to the file to save/append to.
     * @param bool   $isContinuation True if this is the continuation of a download,
     *                               or if we're starting a fresh one.
     *
     * @throws Exception
     * @return array
     */
    public static function downloadChunk($url, $outputPath, $isContinuation)
    {
        // make sure file doesn't already exist if we're starting a new download
        if (!$isContinuation
            && file_exists($outputPath)
        ) {
            throw new Exception(
                Piwik_Translate('General_DownloadFail_FileExists', "'" . $outputPath . "'")
                    . ' ' . Piwik_Translate('General_DownloadPleaseRemoveExisting'));
        }

        // if we're starting a download, get the expected file size & save as an option
        $downloadOption = $outputPath . '_expectedDownloadSize';
        if (!$isContinuation) {
            $expectedFileSizeResult = Piwik_Http::sendHttpRequest(
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
                Piwik::log(sprintf("HEAD request for '%s' failed, got following: %s", $url, print_r($expectedFileSizeResult, true)));
                throw new Exception(Piwik_Translate('General_DownloadFail_HttpRequestFail'));
            }

            Piwik_SetOption($downloadOption, $expectedFileSize);
        } else {
            $expectedFileSize = (int)Piwik_GetOption($downloadOption);
            if ($expectedFileSize === false) // sanity check
            {
                throw new Exception("Trying to continue a download that never started?! That's not supposed to happen...");
            }
        }

        // if existing file is already big enough, then fail so we don't accidentally overwrite
        // existing DB
        $existingSize = file_exists($outputPath) ? filesize($outputPath) : 0;
        if ($existingSize >= $expectedFileSize) {
            throw new Exception(
                Piwik_Translate('General_DownloadFail_FileExistsContinue', "'" . $outputPath . "'")
                    . ' ' . Piwik_Translate('General_DownloadPleaseRemoveExisting'));
        }

        // download a chunk of the file
        $result = Piwik_Http::sendHttpRequest(
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
            Piwik::log("Failed to download range '" . $byteRange[0] . "-" . $byteRange[1]
                . "' of file from url '$url'. Got result: " . print_r($result, true));

            throw new Exception(Piwik_Translate('General_DownloadFail_HttpRequestFail'));
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
        if (file_exists(PIWIK_INCLUDE_PATH . '/core/DataFiles/cacert.pem')) {
            @curl_setopt($ch, CURLOPT_CAINFO, PIWIK_INCLUDE_PATH . '/core/DataFiles/cacert.pem');
        }
    }

    public static function getUserAgent()
    {
        return !empty($_SERVER['HTTP_USER_AGENT'])
            ? $_SERVER['HTTP_USER_AGENT']
            : 'Piwik/' . Piwik_Version::VERSION;
    }

    /**
     * Fetch the file at $url in the destination $destinationPath
     *
     * @param string $url
     * @param string $destinationPath
     * @param int $tries
     * @throws Exception
     * @return bool  true on success, throws Exception on failure
     */
    public static function fetchRemoteFile($url, $destinationPath = null, $tries = 0)
    {
        @ignore_user_abort(true);
        Piwik::setMaxExecutionTime(0);
        return self::sendHttpRequest($url, 10, 'Update', $destinationPath, $tries);
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

        list($name, $value) = $parts;
        $headers[trim($name)] = trim($value);
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
}

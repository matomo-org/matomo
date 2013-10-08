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
namespace Piwik;

/**
 * Http helper: static file server proxy, with compression, caching, isHttps() helper...
 *
 * Used to server piwik.js and the merged+minified CSS and JS files
 *
 * @package Piwik
 */
class ProxyHttp
{
    /**
     * Returns true if the current request appears to be a secure HTTPS connection
     *
     * @return bool
     */
    public static function isHttps()
    {
        return Url::getCurrentScheme() === 'https';
    }

    /**
     * Serve static files through php proxy.
     *
     * It performs the following actions:
     *    - Checks the file is readable or returns "HTTP/1.0 404 Not Found"
     *  - Returns "HTTP/1.1 304 Not Modified" after comparing the HTTP_IF_MODIFIED_SINCE
     *      with the modification date of the static file
     *    - Will try to compress the static file according to HTTP_ACCEPT_ENCODING. Compressed files are store in
     *      the /tmp directory. If compressing extensions are not available, a manually gzip compressed file
     *      can be provided in the /tmp directory. It has to bear the same name with an added .gz extension.
     *      Using manually compressed static files requires you to manually update the compressed file when
     *      the static file is updated.
     *    - Overrides server cache control config to allow caching
     *    - Sends Very Accept-Encoding to tell proxies to store different version of the static file according
     *      to users encoding capacities.
     *
     * Warning:
     *        Compressed filed are stored in the /tmp directory.
     *        If this method is used with two files bearing the same name but located in different locations,
     *        there is a risk of conflict. One file could be served with the content of the other.
     *        A future upgrade of this method would be to recreate the directory structure of the static file
     *        within a /tmp/compressed-static-files directory.
     *
     * @param string $file The location of the static file to serve
     * @param string $contentType The content type of the static file.
     * @param bool $expireFarFuture If set to true, will set Expires: header in far future.
     *                                  Should be set to false for files that don't have a cache buster (eg. piwik.js)
     */
    public static function serverStaticFile($file, $contentType, $expireFarFuture = true)
    {
        if (file_exists($file)) {
            // conditional GET
            $modifiedSince = '';
            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                $modifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'];

                // strip any trailing data appended to header
                if (false !== ($semicolon = strpos($modifiedSince, ';'))) {
                    $modifiedSince = substr($modifiedSince, 0, $semicolon);
                }
            }

            $fileModifiedTime = @filemtime($file);
            $lastModified = gmdate('D, d M Y H:i:s', $fileModifiedTime) . ' GMT';

            // set HTTP response headers
            self::overrideCacheControlHeaders('public');
            @header('Vary: Accept-Encoding');
            @header('Content-Disposition: inline; filename=' . basename($file));

            if ($expireFarFuture) {
                // Required by proxy caches potentially in between the browser and server to cache the request indeed
                @header("Expires: " . gmdate('D, d M Y H:i:s', time() + 86400 * 100) . ' GMT');
            }

            // Returns 304 if not modified since
            if ($modifiedSince === $lastModified) {
                self::setHttpStatus('304 Not Modified');
            } else {
                // optional compression
                $compressed = false;
                $encoding = '';
                $compressedFileLocation = PIWIK_USER_PATH . AssetManager::COMPRESSED_FILE_LOCATION . basename($file);
                $compressedFileLocation = SettingsPiwik::rewriteTmpPathWithHostname($compressedFileLocation);

                $phpOutputCompressionEnabled = ProxyHttp::isPhpOutputCompressed();
                if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && !$phpOutputCompressionEnabled) {
                    $acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'];

                    if (extension_loaded('zlib') && function_exists('file_get_contents') && function_exists('file_put_contents')) {
                        if (preg_match('/(?:^|, ?)(deflate)(?:,|$)/', $acceptEncoding, $matches)) {
                            $encoding = 'deflate';
                            $filegz = $compressedFileLocation . '.deflate';
                        } else if (preg_match('/(?:^|, ?)((x-)?gzip)(?:,|$)/', $acceptEncoding, $matches)) {
                            $encoding = $matches[1];
                            $filegz = $compressedFileLocation . '.gz';
                        }

                        if (!empty($encoding)) {
                            // compress-on-demand and use cache
                            if (!file_exists($filegz) || ($fileModifiedTime > @filemtime($filegz))) {
                                $data = file_get_contents($file);

                                if ($encoding == 'deflate') {
                                    $data = gzdeflate($data, 9);
                                } else if ($encoding == 'gzip' || $encoding == 'x-gzip') {
                                    $data = gzencode($data, 9);
                                }

                                file_put_contents($filegz, $data);
                            }

                            $compressed = true;
                            $file = $filegz;
                        }
                    } else {
                        // manually compressed
                        $filegz = $compressedFileLocation . '.gz';
                        if (preg_match('/(?:^|, ?)((x-)?gzip)(?:,|$)/', $acceptEncoding, $matches) && file_exists($filegz) && ($fileModifiedTime < @filemtime($filegz))) {
                            $encoding = $matches[1];
                            $compressed = true;
                            $file = $filegz;
                        }
                    }
                }

                @header('Last-Modified: ' . $lastModified);

                if (!$phpOutputCompressionEnabled) {
                    @header('Content-Length: ' . filesize($file));
                }

                if (!empty($contentType)) {
                    @header('Content-Type: ' . $contentType);
                }

                if ($compressed) {
                    @header('Content-Encoding: ' . $encoding);
                }

                if (!_readfile($file)) {
                    self::setHttpStatus('505 Internal server error');
                }
            }
        } else {
            self::setHttpStatus('404 Not Found');
        }
    }

    /**
     * Test if php output is compressed
     *
     * @return bool  True if php output is (or suspected/likely) to be compressed
     */
    public static function isPhpOutputCompressed()
    {
        // Off = ''; On = '1'; otherwise, it's a buffer size
        $zlibOutputCompression = ini_get('zlib.output_compression');

        // could be ob_gzhandler, ob_deflatehandler, etc
        $outputHandler = ini_get('output_handler');

        // output handlers can be stacked
        $obHandlers = array_filter(ob_list_handlers(), function ($var) {
            return $var !== "default output handler";
        });

        // user defined handler via wrapper
        $autoPrependFile = ini_get('auto_prepend_file');
        $autoAppendFile = ini_get('auto_append_file');

        return !empty($zlibOutputCompression) ||
        !empty($outputHandler) ||
        !empty($obHandlers) ||
        !empty($autoPrependFile) ||
        !empty($autoAppendFile);
    }


    /**
     * Workaround IE bug when downloading certain document types over SSL and
     * cache control headers are present, e.g.,
     *
     *    Cache-Control: no-cache
     *    Cache-Control: no-store,max-age=0,must-revalidate
     *    Pragma: no-cache
     *
     * @see http://support.microsoft.com/kb/316431/
     * @see RFC2616
     *
     * @param string $override One of "public", "private", "no-cache", or "no-store". (optional)
     */
    public static function overrideCacheControlHeaders($override = null)
    {
        if ($override || self::isHttps()) {
            @header('Pragma: ');
            @header('Expires: ');
            if (in_array($override, array('public', 'private', 'no-cache', 'no-store'))) {
                @header("Cache-Control: $override, must-revalidate");
            } else {
                @header('Cache-Control: must-revalidate');
            }
        }
    }


    /**
     * Set response header, e.g., HTTP/1.0 200 Ok
     *
     * @param string $status Status
     * @return bool
     */
    protected static function setHttpStatus($status)
    {
        if (substr_compare(PHP_SAPI, '-fcgi', -5)) {
            @header($_SERVER['SERVER_PROTOCOL'] . ' ' . $status);
        } else {
            // FastCGI
            @header('Status: ' . $status);
        }
    }

}
<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

/**
 * Http helper: static file server proxy, with compression, caching, isHttps() helper...
 *
 * Used to server piwik.js and the merged+minified CSS and JS files
 *
 */
class ProxyHttp
{
    const DEFLATE_ENCODING_REGEX = '/(?:^|, ?)(deflate)(?:,|$)/';
    const GZIP_ENCODING_REGEX = '/(?:^|, ?)((x-)?gzip)(?:,|$)/';

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
     * @param bool $expireFarFuture Day in the far future to set the Expires header to.
     *                              Should be set to false for files that should not be cached.
     * @param int|false $byteStart The starting byte in the file to serve. If false, the data from the beginning
     *                             of the file will be served.
     * @param int|false $byteEnd The ending byte in the file to serve. If false, the data from $byteStart to the
     *                           end of the file will be served.
     * @param string|false $filename By default the filename of $file is reused as Content-Disposition. If the
     *                               file should be sent as a different filename to the client you can specify
     *                               a custom filename here.
     */
    public static function serverStaticFile($file, $contentType, $expireFarFutureDays = 100, $byteStart = false,
                                            $byteEnd = false, $filename = false)
    {
        // if the file cannot be found return HTTP status code '404'
        if (!file_exists($file)) {
            Common::sendResponseCode(404);
            return;
        }

        if (!is_readable($file)) {
            Common::sendResponseCode(500);
            return;
        }

        $modifiedSince = Http::getModifiedSinceHeader();

        $fileModifiedTime = @filemtime($file);
        $lastModified = gmdate('D, d M Y H:i:s', $fileModifiedTime) . ' GMT';

        // set some HTTP response headers
        self::overrideCacheControlHeaders('public');
        Common::sendHeader('Vary: Accept-Encoding');

        if (false === $filename) {
            $filename = basename($file);
        }

        Common::sendHeader('Content-Disposition: inline; filename=' . $filename);

        if ($expireFarFutureDays) {
            // Required by proxy caches potentially in between the browser and server to cache the request indeed
            Common::sendHeader(self::getExpiresHeaderForFutureDay($expireFarFutureDays));
        }

        // Return 304 if the file has not modified since
        if ($modifiedSince === $lastModified) {
            Common::sendResponseCode(304);
            return;
        }

        // if we have to serve the file, serve it now, either in the clear or compressed
        if ($byteStart === false) {
            $byteStart = 0;
        }

        if ($byteEnd === false) {
            $byteEnd = filesize($file);
        }

        $compressed = false;
        $encoding = '';
        $compressedFileLocation = AssetManager::getInstance()->getAssetDirectory() . '/' . basename($file);

        if (!($byteStart == 0
              && $byteEnd == filesize($file))
        ) {
            $compressedFileLocation .= ".$byteStart.$byteEnd";
        }

        $phpOutputCompressionEnabled = self::isPhpOutputCompressed();
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && !$phpOutputCompressionEnabled) {
            [$encoding, $extension] = self::getCompressionEncodingAcceptedByClient();
            $filegz = $compressedFileLocation . $extension;

            if (self::canCompressInPhp()) {
                if (!empty($encoding)) {
                    // compress the file if it doesn't exist or is newer than the existing cached file, and cache
                    // the compressed result
                    if (self::shouldCompressFile($file, $filegz)) {
                        self::compressFile($file, $filegz, $encoding, $byteStart, $byteEnd);
                    }

                    $compressed = true;
                    $file = $filegz;

                    $byteStart = 0;
                    $byteEnd = filesize($file);
                }
            } else {
                // if a compressed file exists, the file was manually compressed so we just serve that
                if ($extension == '.gz'
                    && !self::shouldCompressFile($file, $filegz)
                ) {
                    $compressed = true;
                    $file = $filegz;

                    $byteStart = 0;
                    $byteEnd = filesize($file);
                }
            }
        }

        Common::sendHeader('Last-Modified: ' . $lastModified);

        if (!$phpOutputCompressionEnabled) {
            Common::sendHeader('Content-Length: ' . ($byteEnd - $byteStart));
        }

        if (!empty($contentType)) {
            Common::sendHeader('Content-Type: ' . $contentType);
        }

        if ($compressed) {
            Common::sendHeader('Content-Encoding: ' . $encoding);
        }

        // in case any notices were triggered before this point (eg in WordPress) etc.
        // it would break the gzipped response since it would have mixed regular notice/string plus gzipped content
        // and would not be able to decode the response
        $levels = ob_get_level();
        for ($i = 0; $i < $levels; $i++) {
            ob_end_clean();
        }

        // clearing all output buffers combined with output compressions had bugs on certain PHP versions
        // manually removing the Content-Encoding header fixes this
        // See https://github.com/php/php-src/issues/8218
        if (
            $phpOutputCompressionEnabled
            && (
                version_compare(PHP_VERSION, '8.0.17', '=')
                || version_compare(PHP_VERSION, '8.0.18', '=')
                || version_compare(PHP_VERSION, '8.1.4', '=')
                || version_compare(PHP_VERSION, '8.1.5', '=')
            )
        ) {
            header_remove("Content-Encoding");
        }

        if (!_readfile($file, $byteStart, $byteEnd)) {
            Common::sendResponseCode(500);
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
        if (!defined('PIWIK_TEST_MODE')) {
            $autoPrependFile = ini_get('auto_prepend_file');
            $autoAppendFile = ini_get('auto_append_file');
        }

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
            Common::stripHeader('Pragma');
            Common::stripHeader('Expires');
            if (in_array($override, array('public', 'private', 'no-cache', 'no-store'))) {
                Common::sendHeader("Cache-Control: $override, must-revalidate");
            } else {
                Common::sendHeader('Cache-Control: must-revalidate');
            }
        }
    }

    /**
     * Returns a formatted Expires HTTP header for a certain number of days in the future. The result
     * can be used in a call to `header()`.
     */
    private static function getExpiresHeaderForFutureDay($expireFarFutureDays)
    {
        return "Expires: " . gmdate('D, d M Y H:i:s', time() + 86400 * (int)$expireFarFutureDays) . ' GMT';
    }

    private static function getCompressionEncodingAcceptedByClient()
    {
        $acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'];
        if (preg_match(self::GZIP_ENCODING_REGEX, $acceptEncoding, $matches)) {
            return array('gzip', '.gz');
        } elseif (preg_match(self::DEFLATE_ENCODING_REGEX, $acceptEncoding, $matches)) {
            return array('deflate', '.deflate');
        } else {
            return array(false, false);
        }
    }

    private static function canCompressInPhp()
    {
        return extension_loaded('zlib') && function_exists('file_get_contents') && function_exists('file_put_contents');
    }

    private static function shouldCompressFile($fileToCompress, $compressedFilePath)
    {
        $toCompressLastModified = @filemtime($fileToCompress);
        $compressedLastModified = @filemtime($compressedFilePath);

        return !file_exists($compressedFilePath) || ($toCompressLastModified > $compressedLastModified);
    }

    private static function compressFile($fileToCompress, $compressedFilePath, $compressionEncoding, $byteStart,
                                         $byteEnd)
    {
        $data = file_get_contents($fileToCompress);
        $data = substr($data, $byteStart, $byteEnd - $byteStart);

        if ($compressionEncoding == 'deflate') {
            $data = gzdeflate($data, 9);
        } elseif ($compressionEncoding == 'gzip' || $compressionEncoding == 'x-gzip') {
            $data = self::gzencode($data);
        }

        if (false === $data) {
            throw new \Exception('compressing file '.$fileToCompress.' failed');
        }

        file_put_contents($compressedFilePath, $data);
    }

    public static function gzencode($data)
    {
        return gzencode($data, 9);
    }
}

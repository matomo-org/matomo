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
 * Proxy headers
 *
 */
class ProxyHeaders
{
    /**
     * Get protocol information, with the exception of HTTPS
     *
     * @return string protocol information
     */
    public static function getProtocolInformation()
    {
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
            return 'SERVER_PORT=443';
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') {
            return 'X-Forwarded-Proto';
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_SCHEME']) && strtolower($_SERVER['HTTP_X_FORWARDED_SCHEME']) == 'https') {
            return 'X-Forwarded-Scheme';
        }

        if (isset($_SERVER['HTTP_X_URL_SCHEME']) && strtolower($_SERVER['HTTP_X_URL_SCHEME']) == 'https') {
            return 'X-Url-Scheme';
        }

        return null;
    }

    /**
     * Get headers present in the HTTP request
     *
     * @param array $recognizedHeaders
     * @return array HTTP headers
     */
    private static function getHeaders($recognizedHeaders)
    {
        $headers = array();

        foreach ($recognizedHeaders as $header) {
            if (isset($_SERVER[$header])) {
                $headers[] = $header;
            }
        }

        return $headers;
    }

    /**
     * Detect proxy client headers
     *
     * @return array Proxy client HTTP headers
     */
    public static function getProxyClientHeaders()
    {
        return self::getHeaders(array(
                                     'HTTP_CF_CONNECTING_IP',
                                     'HTTP_CLIENT_IP',
                                     'HTTP_X_FORWARDED_FOR',
                                ));
    }

    /**
     * Detect proxy host headers
     *
     * @return array Proxy host HTTP headers
     */
    public static function getProxyHostHeaders()
    {
        return self::getHeaders(array(
                                     'HTTP_X_FORWARDED_HOST',
                                ));
    }
}

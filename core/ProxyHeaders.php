<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
     * @return ?string protocol information
     */
    public static function getProtocolInformation(): ?string
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
     * @param string[] $recognizedHeaders
     * @return string[] HTTP headers
     */
    private static function getHeaders(array $recognizedHeaders): array
    {
        $headers = [];

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
     * @return string[] Proxy client HTTP headers
     */
    public static function getProxyClientHeaders(): array
    {
        return self::getHeaders([
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
        ]);
    }

    /**
     * Detect proxy host headers
     *
     * @return string[] Proxy host HTTP headers
     */
    public static function getProxyHostHeaders(): array
    {
        return self::getHeaders([
            'HTTP_X_FORWARDED_HOST',
        ]);
    }

    /**
     * Detect proxy scheme headers
     *
     * @return string[] Proxy scheme HTTP headers
     */
    public static function getProxySchemeHeaders(): array
    {
        return self::getHeaders([
            'HTTP_X_FORWARDED_PROTO',
            'HTTP_X_FORWARDED_SCHEME',
            'HTTP_X_URL_SCHEME',
        ]);
    }
}

<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Installation;

use InvalidArgumentException;

class HostPortExtractor
{
    public $host;
    public $port;

    private function __construct(string $host, string $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Extracts the correctly formatted host and port values from the user-provided
     * database server host.
     *
     * @param string $dbHost The user provided host to extract values from
     * @return HostPortExtractor|null Extracted Host and Port, null if nothing can be extracted
     */
    public static function extract(string $dbHost): ?HostPortExtractor
    {
        try {
            if (self::isIPv6($dbHost)) {
                return self::extractIPv6($dbHost);
            } elseif (self::isUnixSocket($dbHost)) {
                return self::extractUnixSocket($dbHost);
            } elseif (self::isIPWithPort($dbHost)) {
                return self::extractIPAndPort($dbHost);
            } else {
                return null;
            }
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * Determines if the provided host is correctly formatted IPv6
     *
     * @param string $dbHost The user provided database server host
     * @return boolean Whether the provided host is correct IPv6
     */
    private static function isIPv6(string $dbHost): bool
    {
        // filter_var requires potential IPv6 addresses to not be encased
        preg_match_all('/^\[(.*?)\]/', $dbHost, $matches);
        $listOfTextInsideSquareBrackets = $matches[1];

        /*
         * Only return true if there is some text inside square brackets,
         * and that text is considered valid IPv6 as per filter_var()
         */
        if (
            count($listOfTextInsideSquareBrackets) > 0 &&
            filter_var(
                $listOfTextInsideSquareBrackets[0],
                FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV6
            ) !== false
        ) {
            return true;
        }
        return false;
    }

    /**
     * Extracts the Host and Port from a user provided database server host,
     * assuming the provided host is a IPv6 address, with or without a port
     *
     * @param string $dbHost The user provided database server host
     * @throws InvalidArgumentException if the provided dbHost is not a valid IPv6
     * @return HostPortExtractor The extracted Host & Port
     */
    private static function extractIPv6(string $dbHost): HostPortExtractor
    {
        if (!self::isIPv6($dbHost)) {
            throw new InvalidArgumentException('$dbHost must be a valid IPv6 address');
        }

        // find and extract the text inside square brackets: []
        preg_match_all('/\[(.*?)\]/', $dbHost, $matches);
        $listOfTextInsideSquareBrackets = $matches[1];

        $port = '';

        // extract text after closing bracket to search for port
        $components = explode(']', $dbHost);
        if (count($components) > 1 && strlen($components[1]) > 0) {
            // check if the text is a valid port e.g. ':3000'
            preg_match('/^:(\d+)$/', $components[1], $portMatches);
            if (count($portMatches) > 0) {
                $port = $portMatches[1];
            } else {
                throw new InvalidArgumentException('$dbHost port is invalid');
            }
        }
        // db connector requires IPv6 to be encased in square brackets
        $host = '[' . $listOfTextInsideSquareBrackets[0] . ']';

        return new HostPortExtractor($host, $port);
    }

    /**
     * Determines if the provided host is a Unix Socket
     *
     * @param string $dbHost The user provided database server host
     * @return boolean Whether the provided host is a unix socket
     */
    private static function isUnixSocket(string $dbHost): bool
    {
        return (strpos($dbHost, '/') !== false);
    }

    /**
     * Extracts the host & port from the user provided database server host,
     * assuming the provided host is a unix socket
     *
     * @param string $dbHost The user provided database server host
     * @throws InvalidArgumentException if the provided dbHost is not a valid Unix Socket
     * @return HostPortExtractor The extracted Host & Port
     */
    private static function extractUnixSocket(string $dbHost): HostPortExtractor
    {
        if (!self::isUnixSocket($dbHost)) {
            throw new InvalidArgumentException('$dbHost must be a valid Unix Socket');
        }

        /*
         * The DB connector requires unix sockets to be provided as ports in
         * order to connect successfully.
         */
        $portIndex = strpos($dbHost, '/');
        $port = substr($dbHost, $portIndex);

        return new HostPortExtractor('', $port);
    }

    /**
     * Determines if the provided host is a standard web address or IP with a port
     *
     * @param string $dbHost The user provided database server host
     * @return boolean Whether the provided host is a standard address or IP with port
     */
    private static function isIPWithPort(string $dbHost): bool
    {
        $numColons = substr_count($dbHost, ':');
        return ($numColons === 1);
    }

    /**
     * Extracts the host & port from the user provided database server host,
     * assuming the provided host is a standard address or IP with a port
     *
     * @param string $dbHost The user provided database server host
     * @throws InvalidArgumentException if the provided dbHost is not a valid address with a port
     * @return HostPortExtractor The extracted host & port
     */
    private static function extractIPAndPort(string $dbHost): HostPortExtractor
    {
        if (!self::isIPWithPort($dbHost)) {
            throw new InvalidArgumentException('$dbHost must be a valid address');
        }

        [$host, $port] = explode(':', $dbHost);

        return new HostPortExtractor($host, $port);
    }
}

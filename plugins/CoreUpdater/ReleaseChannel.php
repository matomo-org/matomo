<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater;

use Piwik\Db;
use Piwik\Http;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\UpdateCheck\ReleaseChannel as BaseReleaseChannel;
use Piwik\Url;
use Piwik\Version;

abstract class ReleaseChannel extends BaseReleaseChannel
{
    public const ANONYMISED_URL_IP_LOCAL_PREFIX = 'IP-LOCAL:';
    public const ANONYMISED_URL_IP_PUBLIC_PREFIX = 'IP-PUBLIC:';

    public function getUrlToCheckForLatestAvailableVersion()
    {
        $parameters = array(
            'piwik_version'   => Version::VERSION,
            'php_version'     => PHP_VERSION,
            'mysql_version'   => Db::get()->getServerVersion(),
            'release_channel' => $this->getId(),
            'url'             => self::anonymiseUrl(Url::getCurrentUrlWithoutQueryString()),
        );

        $url = Client::getApiServiceUrl()
            . '/1.0/getLatestVersion/'
            . '?' . Http::buildQuery($parameters);

        return $url;
    }

    /**
     * Anonymises an installation URL for the update check API.
     *
     * Returns:
     *   - empty string for URLs without a host or with a host we have always excluded
     *     from update-check stats (no-dot hosts, `example.org`, `localhost`, ...);
     *   - `IP-LOCAL:` + SHA-256 of the host for private/reserved IP hosts;
     *   - `IP-PUBLIC:` + SHA-256 of the host for public IP hosts;
     *   - SHA-256 of the lowercased host otherwise.
     *
     * The hash input is the host alone (lowercased, IPv6 brackets stripped). This
     * matches the historical "one host = one install" semantics of the API
     * (`api_update_check.host` has always stored just the parsed host, never the
     * full URL), so backfilled rows and new rows produce identical hashes for the
     * same install.
     */
    public static function anonymiseUrl(string $url): string
    {
        if ($url === '') {
            return '';
        }

        $parts = @parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            return '';
        }

        $host = strtolower($parts['host']);
        $hostForIpCheck = (strlen($host) > 1 && $host[0] === '[' && substr($host, -1) === ']')
            ? substr($host, 1, -1)
            : $host;

        $isIp = (bool) filter_var($hostForIpCheck, FILTER_VALIDATE_IP);

        if (!$isIp && self::isExcludedHost($hostForIpCheck)) {
            return '';
        }

        $hash = hash('sha256', $isIp ? $hostForIpCheck : $host);

        if ($isIp) {
            $isPublic = (bool) filter_var(
                $hostForIpCheck,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );
            return ($isPublic ? self::ANONYMISED_URL_IP_PUBLIC_PREFIX : self::ANONYMISED_URL_IP_LOCAL_PREFIX) . $hash;
        }

        return $hash;
    }

    private static function isExcludedHost(string $host): bool
    {
        if ($host === '' || $host === 'example.org' || strpos($host, '.') === false) {
            return true;
        }
        return false;
    }

    public function getDownloadUrlWithoutScheme($version)
    {
        if (!empty($version)) {
            return sprintf('://builds.matomo.org/matomo-%s.zip', $version);
        }

        return '://builds.matomo.org/matomo.zip';
    }
}
